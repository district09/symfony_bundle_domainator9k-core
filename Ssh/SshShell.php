<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\AbstractAuth;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;

class SshShell implements ShellInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port = 22;

    /**
     * @var AbstractAuth
     */
    protected $auth;

    /**
     * @var \phpseclib\Net\SSH2
     */
    protected $connection;

    /**
     * @var resource
     */
    protected $sftp;

    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * @param string       $host
     * @param AbstractAuth $auth
     */
    public function __construct($host, AbstractAuth $auth)
    {
        $this->host = $host;
        $this->auth = $auth;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return AbstractAuth
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param AbstractAuth $auth
     *
     * @return $this
     */
    public function setAuth(AbstractAuth $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param bool $authenticate
     */
    public function connect($authenticate = true)
    {
        if ($this->connection) {
            return;
        }

        //First try a direct ping
//        $exec = exec("ping -c 3 -s 64 -t 64 ".$this->host);
//        $pings = explode("=", $exec);
//        $pingVal = explode("/", end($pings));
//        if (!isset($pingVal[1])) {
//            throw new \RuntimeException("ping to host failed");
//        }

        $this->connection = new SSH2($this->host, $this->port, $this->timeout);
        $this->connection->_connect();

        if (!$this->connection->isConnected()) {
            throw new \RuntimeException(sprintf(
                'ssh: unable to establish connection to %s on port %s',
                $this->host,
                $this->port
            ));
        }

        if ($authenticate) {
            $this->authenticate();
        }
    }

    /**
     * @throws \RuntimeException
     */
    protected function assertConnection()
    {
        if (!($this->connection instanceof SSH2)) {
            throw new \RuntimeException('no connection available');
        }
    }

    /**
     * @return \phpseclib\Net\SFTP
     */
    public function getSFtp()
    {
        $this->assertConnection();

        if (!$this->sftp) {
            $this->sftp = new SFTP($this->host, $this->port, $this->timeout);
            $this->auth->authenticate($this->sftp);
        }

        return $this->sftp;
    }

    public function authenticate()
    {
        $this->assertConnection();

        $this->auth->authenticate($this->connection);
    }

    public function disconnect()
    {
        $this->assertConnection();
        $this->connection->disconnect();
        $this->connection = null;
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command, &$stdout = null, &$exitStatus = null, &$stderr = null)
    {
        $this->assertConnection();

        // @see http://stackoverflow.com/questions/10478491/php-ssh2-exec-channel-exit-status
        $exitCatcher = '__CATCH__EXIT_CODE__';
        $cmd = "($command); echo \"$exitCatcher\"\$?";
        $stdout = $this->connection->exec($cmd);
        $stderr = $this->connection->getStdError();

        $match = preg_match('/'.$exitCatcher.'(\d*)$/', $stdout, $statusSearch);
        $exitStatus = null;
        if ($match === 1) {
            $exitStatus = (int) $statusSearch[1];
            $stdout = explode($exitCatcher, $stdout);
            $stdout = $stdout[0];
        }

        return $exitStatus === 0;
    }

    /**
     * @param string $file absolute path to file
     *
     * @return array|false
     */
    public function stat($file)
    {
        $sftp = $this->getSFtp();

        $stat = $sftp->stat($file);
        if ($stat) {
            $stat['type'] = ($stat['mode'] & 040000) ? 'dir' : 'file';
        }

        return $stat;
    }

    /**
     * @param string $file absolute path to file
     *
     * @return array|false
     */
    public function fileExists($file)
    {
        return $this->getSFtp()->file_exists($file);
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    public function filePutContent($file, $content)
    {
        return $this->getSFtp()->put($file, $content);
    }

    /**
     * @param string $directory absolute path to file
     * @param int    $mode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function mkdir($directory, $mode = 0777, $recursive = false)
    {
        return $this->getSFtp()->mkdir($directory, $mode, $recursive);
    }

    /**
     * @param string $path absolute path to file or directory
     * @param int    $mode
     *
     * @return bool
     */
    public function chmod($path, $mode = 0777)
    {
        return $this->getSFtp()->chmod($mode, $path);
    }
}

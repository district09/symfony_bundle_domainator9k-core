<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\AbstractAuth;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshFactoryInterface;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;
use RuntimeException;

class SshShell implements SshShellInterface
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
     * @var SSH2
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
     * @var SshFactoryInterface
     */
    protected $sshFactory;

    /**
     * @param string       $host
     * @param AbstractAuth $auth
     */
    public function __construct($host, AbstractAuth $auth, SshFactoryInterface $sshFactory)
    {
        $this->host = $host;
        $this->auth = $auth;
        $this->sshFactory = $sshFactory;
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

        $this->connection = $this->sshFactory->getSshConnection($this->host, $this->port, $this->timeout);
        $this->connection->_connect();

        if (!$this->connection->isConnected()) {
            throw new RuntimeException(sprintf(
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
     * @throws RuntimeException
     */
    protected function assertConnection()
    {
        if (!($this->connection instanceof SSH2)) {
            throw new RuntimeException('No connection available.');
        }
    }

    /**
     * @return SFTP
     */
    public function getSFtp()
    {
        $this->assertConnection();

        if (!$this->sftp) {
            $this->sftp = $this->sshFactory->getSftpConnection($this->host, $this->port, $this->timeout);
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
        $stdout = $this->connection->exec($command);
        $stderr = $this->connection->getStdError();
        $exitStatus = $this->connection->getExitStatus();

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

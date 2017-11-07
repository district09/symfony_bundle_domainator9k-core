<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\AbstractAuth;
use phpseclib\Net\SFTP;

interface SshShellInterface
{
    /**
     * @param string    $command
     * @param string    &$stdout     will be filled with the command output STDOUT
     * @param bool|null &$exitStatus will be filled with the command exit status or null of unknown
     * @param string    &$stderr     will be filled with the command error output STDERR
     *
     * @return bool true if exit code === 0
     */
    public function exec($command, &$stdout = null, &$exitStatus = null, &$stderr = null);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host);

    /**
     * @return int
     */
    public function getPort();

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port);

    /**
     * @return AbstractAuth
     */
    public function getAuth();

    /**
     * @param AbstractAuth $auth
     *
     * @return $this
     */
    public function setAuth(AbstractAuth $auth);

    /**
     * @return int
     */
    public function getTimeout();

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout);

    /**
     * @param bool $authenticate
     */
    public function connect($authenticate = true);

    /**
     * @return SFTP
     */
    public function getSFtp();

    public function authenticate();

    public function disconnect();

    /**
     * @param string $file absolute path to file
     *
     * @return array|false
     */
    public function stat($file);

    /**
     * @param string $file absolute path to file
     *
     * @return array|false
     */
    public function fileExists($file);

    /**
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    public function filePutContent($file, $content);

    /**
     * @param string $directory absolute path to file
     * @param int    $mode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function mkdir($directory, $mode = 0777, $recursive = false);

    /**
     * @param string $path absolute path to file or directory
     * @param int    $mode
     *
     * @return bool
     */
    public function chmod($path, $mode = 0777);
}

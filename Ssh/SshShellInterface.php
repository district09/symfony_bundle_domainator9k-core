<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\AbstractAuth;
use phpseclib\Net\SFTP;

interface SshShellInterface
{

    /**
     * @param string $command
     * @param string $stdout
     *     Will be filled with the command output STDOUT.
     * @param bool|null $exitStatus
     *     Will be filled with the command exit status or null if unknown.
     * @param string $stderr
     *     Will be filled with the command error output STDERR.
     *
     * @return bool
     *     True if exit code === 0, false otherwise.
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

    /**
     * Login to the remote shell.
     */
    public function authenticate();

    /**
     * Disconnect from the remote shell.
     */
    public function disconnect();

    /**
     * @param string $file absolute path to file
     *
     * @return array|false
     */
    public function stat($file);

    /**
     * @param string $file
     *     Absolute path to a file.
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
     * @param string $directory
     *     Absolute path to a directory
     * @param int    $mode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function mkdir($directory, $mode = 0777, $recursive = false);

    /**
     * @param string $path
     *     Absolute path to a file or a directory.
     * @param int $mode
     *
     * @return bool
     */
    public function chmod($path, $mode = 0777);
}

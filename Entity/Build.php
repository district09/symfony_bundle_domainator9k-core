<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Ctrl\RadBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="build_log")
 */
class Build
{
    const TYPE_PROVISION = 'provision';
    const TYPE_DEPLOY = 'deploy';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", type="string", nullable=false)
     */
    protected $type;

    /**
     * @var Application
     * @ORM\ManyToOne(targetEntity="Application", cascade={"all"}, inversedBy="builds")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $application;

    /**
     * @var \DateTime
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    protected $timestamp;

    /**
     * @var string
     * @ORM\Column(name="log", type="text", nullable=true)
     */
    protected $log;

    /**
     * @var bool
     * @ORM\Column(name="started", type="boolean", options={"default"="0"})
     */
    protected $started = false;

    /**
     * @var bool
     * @ORM\Column(name="completed", type="boolean", options={"default"="0"})
     */
    protected $completed = false;

    /**
     * @var bool
     * @ORM\Column(name="success", type="boolean", options={"default"="0"})
     */
    protected $success = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Ctrl\RadBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @var int
     * @ORM\Column(name="pid", type="integer", nullable=true, options={"default"=NULL})
     */
    protected $pid;

    /**
     * @var string
     * @ORM\Column(name="deleted_user", type="string")
     */
    protected $deletedUser;

    public function __construct(Application $app, $type, AppEnvironment $env = null)
    {
        $this->application = $app;
        $this->appEnvironment = $env;
        $this->type = $type;

        if ($type === self::TYPE_DEPLOY && !$env) {
            throw new \InvalidArgumentException(sprintf(
                'environment is required when creating a DEPLOY build'
            ));
        }

        $this->timestamp = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        $this->deletedUser = $user->getUsername();

        return $this;
    }

    /**
     * @return string
     */
    public function getDeletedUser()
    {
        return $this->deletedUser;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     *
     * @return $this
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return AppEnvironment
     */
    public function getAppEnvironment()
    {
        return $this->appEnvironment;
    }

    /**
     * @param mixed $appEnvironment
     *
     * @return $this
     */
    public function setAppEnvironment($appEnvironment)
    {
        $this->appEnvironment = $appEnvironment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     *
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     *
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * @param bool $started
     *
     * @return $this
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     *
     * @return $this
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
        $this->pid = null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     *
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     *
     * @return $this
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }
}

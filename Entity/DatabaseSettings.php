<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="database_settings")
 */
class DatabaseSettings
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AppEnvironment
     * @ORM\OneToOne(targetEntity="AppEnvironment", cascade={"all"}, inversedBy="databaseSettings")
     * * @ORM\JoinColumn(name="environment_id", referencedColumnName="id", nullable=true)
     */
    protected $appEnvironment;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="255")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="host", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $host = 'localhost';

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $engine = 'mysql';

    /**
     * @var string
     * @ORM\Column(name="port", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="5")
     */
    protected $port = '3306';

    /**
     * @var string
     * @ORM\Column(name="user", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     */
    protected $user = 'root';

    /**
     * @var string
     * @ORM\Column(name="password", type="string", nullable=true)
     * @Assert\Length(min="1", max="255")
     */
    protected $password;

    /**
     * @var int
     * @ORM\Column(name="sock_database_id", type="integer", nullable=true)
     */
    protected $sockDatabaseId;

    /**
     * @var bool
     * @ORM\Column(name="is_created", type="boolean", options={"default"="0"})
     */
    protected $isCreated = false;

    /**
     * @param AppEnvironment $appEnvironment
     * @param string         $name
     */
    public function __construct(AppEnvironment $appEnvironment, $name)
    {
        $this->appEnvironment = $appEnvironment;
        $this->name = $name;
        $this->user = substr($appEnvironment->getApplication()->getNameCanonical(), 0, 14).'_'.substr($appEnvironment->getNameCanonical(), 0, 1);

        // generate a random string, hopefully, somebody will set a real password later on...
        $this->password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AppEnvironment
     */
    public function getAppEnvironment()
    {
        return $this->appEnvironment;
    }

    /**
     * @param AppEnvironment $appEnvironment
     *
     * @return $this
     */
    public function setAppEnvironment($appEnvironment)
    {
        $this->appEnvironment = $appEnvironment;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return int
     */
    public function getSockDatabaseId()
    {
        return $this->sockDatabaseId;
    }

    /**
     * @param int $sockDatabaseId
     *
     * @return $this
     */
    public function setSockDatabaseId($sockDatabaseId)
    {
        $this->sockDatabaseId = $sockDatabaseId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isCreated()
    {
        return $this->isCreated;
    }

    /**
     * @param mixed $isCreated
     *
     * @return $this
     */
    public function setIsCreated($isCreated)
    {
        $this->isCreated = $isCreated;

        return $this;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        if (!$this->engine) {
            $this->engine = 'mysql';
        }

        return $this->engine;
    }

    /**
     * @param string $engine
     *
     * @return $this
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }
}

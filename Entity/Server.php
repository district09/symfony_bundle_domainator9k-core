<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="server")
 */
class Server
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="sock_id", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $sockId;

    /**
     * @var bool
     * @ORM\Column(name="manage_sock", type="boolean", options={"default": 0})
     */
    protected $manageSock = false;

    /**
     * @var bool
     * @ORM\Column(name="task_server", type="boolean", options={"default": 0})
     */
    protected $taskServer = false;

    /**
     * @var string
     * @ORM\Column(name="ip", type="string", length=20, nullable=false)
     * @Assert\NotBlank()
     */
    protected $ip;

    /**
     * @var string
     * @ORM\Column(name="environment", type="string", length=20, nullable=false)
     * @Assert\NotBlank()
     */
    protected $environment;

    /**
     * @param string $sockId
     * @param string $name
     * @param string $ip
     * @param string $environment
     */
    public function __construct($sockId, $name, $ip, $environment)
    {
        $this->sockId = $sockId;
        $this->name = $name;
        $this->ip = $ip;
        $this->environment = $environment;
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
     *
     * @todo Shouldn't this be in a separate sock bundle??
     */
    public function getSockId()
    {
        return $this->sockId;
    }

    /**
     * @param string $sockId
     *
     * @return $this
     *
     * @todo Shouldn't this be in a separate sock bundle??
     */
    public function setSockId($sockId)
    {
        $this->sockId = $sockId;

        return $this;
    }

    /**
     * @return bool
     */
    public function manageSock()
    {
        return $this->manageSock;
    }

    /**
     * @param bool $manageSock
     *
     * @return $this
     */
    public function setManageSock($manageSock)
    {
        $this->manageSock = $manageSock;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTaskServer()
    {
        return $this->taskServer;
    }

    /**
     * @param bool $taskServer
     *
     * @return $this
     */
    public function setTaskServer($taskServer)
    {
        $this->taskServer = $taskServer;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="server")
 */
class Server
{

    use SettingImplementationTrait;

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
     * @var boolean
     * @ORM\Column(name="task_server", type="boolean")
     */
    protected $taskServer;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ApplicationServer",mappedBy="server",cascade={"remove"})
     */
    protected $applicationServers;

    /**
     * Gets the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name.
     *
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
     * Gets the server IP.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Sets the server IP.
     *
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
     * Gets the environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Sets the environment.
     *
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

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
     */
    public function setTaskServer($taskServer)
    {
        $this->taskServer = $taskServer;
    }

    /**
     * @param ApplicationServer $applicationServer
     */
    public function addApplicationServer(ApplicationServer $applicationServer){
        $this->applicationServers->add($applicationServer);
    }

    /**
     * @return ArrayCollection
     */
    public function getApplicationServers(){
        return $this->applicationServers;
    }

}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasRoles;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasUsers;
use DigipolisGent\Domainator9k\CoreBundle\Tools\StringHelper;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="application_environment")
 */
class ApplicationEnvironment
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
     * @var AbstractApplication
     *
     * @ORM\ManyToOne(targetEntity="AbstractApplication", inversedBy="applicationEnvironments")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @var Environment
     *
     * @ORM\ManyToOne(targetEntity="Environment",inversedBy="applicationEnvironments")
     * @ORM\JoinColumn(name="environment_id",referencedColumnName="id")
     */
    protected $environment;

    /**
     * @var string
     *
     * @ORM\Column(name="database_name",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $databaseName;

    /**
     * @var string
     *
     * @ORM\Column(name="database_host",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $databaseHost;

    /**
     * @var integer
     *
     * @ORM\Column(name="database_port",type="integer",nullable=true)
     * @Assert\NotBlank()
     */
    protected $databasePort;

    /**
     * @var string
     *
     * @ORM\Column(name="database_engine",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $databaseEngine;

    /**
     * @var string
     *
     * @ORM\Column(name="database_user",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $databaseUser;

    /**
     * @var string
     *
     * @ORM\Column(name="database_password",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $databasePassword;

    /**
     * @var string
     *
     * @ORM\Column(name="git_ref",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $gitRef;

    /**
     * @param AbstractApplication $application
     */
    public function setApplication(AbstractApplication $application)
    {
        $this->application = $application;
    }

    /**
     * @return AbstractApplication
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getEnvironmentName()
    {
        return $this->getEnvironment()->getName();
    }

    /**
     * @return string
     */
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * @param string $databaseHost
     */
    public function setDatabaseHost(string $databaseHost)
    {
        $this->databaseHost = $databaseHost;
    }

    /**
     * @return int
     */
    public function getDatabasePort()
    {
        return $this->databasePort;
    }

    /**
     * @param int $databasePort
     */
    public function setDatabasePort(int $databasePort)
    {
        $this->databasePort = $databasePort;
    }

    /**
     * @return string
     */
    public function getDatabaseEngine()
    {
        return $this->databaseEngine;
    }

    /**
     * @param string $databaseEngine
     */
    public function setDatabaseEngine(string $databaseEngine)
    {
        $this->databaseEngine = $databaseEngine;
    }

    /**
     * @return string
     */
    public function getDatabaseUser()
    {
        return $this->databaseUser;
    }

    /**
     * @param string $databaseUser
     */
    public function setDatabaseUser(string $databaseUser)
    {
        $this->databaseUser = $databaseUser;
    }

    /**
     * @return string
     */
    public function getDatabasePassword()
    {
        return $this->databasePassword;
    }

    /**
     * @param string $databasePassword
     */
    public function setDatabasePassword(string $databasePassword)
    {
        $this->databasePassword = $databasePassword;
    }

    /**
     * @return string
     */
    public function getGitRef()
    {
        return $this->gitRef;
    }

    /**
     * @param string $gitRef
     */
    public function setGitRef(string $gitRef)
    {
        $this->gitRef = $gitRef;
    }
}

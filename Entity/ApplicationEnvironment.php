<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="application_environment")
 */
class ApplicationEnvironment
{

    use SettingImplementationTrait;
    use IdentifiableTrait;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Build",mappedBy="applicationEnvironment",cascade={"remove"})
     */
    protected $builds;

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
     */
    protected $databaseName;

    /**
     * @var string
     * @ORM\Column(name="database_user",type="string",nullable=true)
     */
    protected $databaseUser;

    /**
     * @var string
     *
     * @ORM\Column(name="database_password",type="string",nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="domain",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $domain;

    public function __construct()
    {
        $this->builds = new ArrayCollection();
    }

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
    public function setDatabaseName(string $databaseName = null)
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
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->getEnvironment()->getName();
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
    public function setDatabaseUser(string $databaseUser = null)
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
    public function setDatabasePassword(string $databasePassword = null)
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

    /**
     * @return ArrayCollection
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }
}

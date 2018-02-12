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
class ApplicationEnvironment implements TemplateInterface
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Task",mappedBy="applicationEnvironment",cascade={"remove"})
     */
    protected $tasks;

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
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return string
     */
    public static function getSettingImplementationName()
    {
        return 'application_environment';
    }

    /**
     * @return array
     */
    public static function getTemplateReplacements(): array
    {
        return [
            'serverIps()' => 'getServerIps()',
            'environmentName()' => 'getEnvironment().getName()',
            'config(key)' => 'getConfig(key)',
            'databaseName()' => 'getDatabaseName()',
            'databaseUser()' => 'getDatabaseUser()',
            'databasePassword()' => 'getDatabasePassword()',
        ];
    }

    /**
     * @return AbstractApplication
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param AbstractApplication $application
     */
    public function setApplication(AbstractApplication $application)
    {
        $this->application = $application;
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
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->getEnvironment()->getName();
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
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
    public function getTasks()
    {
        return $this->tasks;
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

    /**
     * @return string
     */
    public function getServerIps(): string
    {
        $serverIps = [];
        /** @var Server $server */
        foreach ($this->getEnvironment()->getServers() as $server) {
            $serverIps[] = $server->getHost();
        }

        return implode(' ', $serverIps);
    }

    public function getConfig($key)
    {
        foreach ($this->getSettingDataValues() as $settingDataValue) {
            if ($settingDataValue->getSettingDataType()->getKey() == $key) {
                return $settingDataValue->getValue();
            }
        }

        return '';
    }
}

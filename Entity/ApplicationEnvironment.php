<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\TemplateImplementationTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'application_environment')]
#[ORM\Entity]
class ApplicationEnvironment implements TemplateInterface
{

    use SettingImplementationTrait;
    use IdentifiableTrait;
    use TemplateImplementationTrait;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \Task::class, mappedBy: 'applicationEnvironment', cascade: ['remove'])]
    protected $tasks;

    /**
     * @var AbstractApplication
     */
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \AbstractApplication::class, inversedBy: 'applicationEnvironments')]
    protected $application;

    /**
     * @var Environment
     */
    #[ORM\JoinColumn(name: 'environment_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Environment::class, inversedBy: 'applicationEnvironments')]
    protected $environment;

    /**
     * @var string
     */
    #[ORM\Column(name: 'database_name', type: 'string', nullable: true)]
    protected $databaseName;

    /**
     * @var string
     */
    #[ORM\Column(name: 'database_user', type: 'string', nullable: true)]
    protected $databaseUser;

    /**
     * @var string
     */
    #[ORM\Column(name: 'database_password', type: 'string', nullable: true)]
    protected $databasePassword;

    /**
     * @var string
     */
    #[ORM\Column(name: 'git_ref', type: 'string', nullable: true)]
    #[Assert\NotBlank]
    protected $gitRef;

    /**
     * @var string
     */
    #[ORM\Column(name: 'domain', type: 'string', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*\.[a-z]{2,63}$/', message: 'The domain is not valid')]
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
     * @return AbstractApplication
     */
    public function getApplication(): ?AbstractApplication
    {
        return $this->application;
    }

    /**
     * @param AbstractApplication $application
     */
    public function setApplication(AbstractApplication $application = null)
    {
        $this->application = $application;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): ?string
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
    public function getEnvironmentName(): ?string
    {
        return $this->getEnvironment()->getName();
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): ?Environment
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
    public function getDatabaseUser(): ?string
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
    public function getDatabasePassword(): ?string
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
    public function getGitRef(): ?string
    {
        return $this->gitRef;
    }

    /**
     * @param string $gitRef
     */
    public function setGitRef(string $gitRef = null)
    {
        $this->gitRef = $gitRef;
    }

    /**
     * @return ArrayCollection
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * @return string
     */
    public function getDomain(): ?string
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
        /** @var VirtualServer $server */
        foreach ($this->getEnvironment()->getVirtualServers() as $server) {
            $serverIps[] = $server->getHost();
        }

        return implode(' ', $serverIps);
    }

    /**
     * @return string
     */
    public function getWorkerServerIp(): string
    {
        /** @var VirtualServer $server */
        $servers = $this->getEnvironment()->getVirtualServers();
        foreach ($servers as $server) {
            if ($server->isTaskServer()) {
                return $server->getHost();
            }
        }

        return end($servers)->getHost();
    }
}

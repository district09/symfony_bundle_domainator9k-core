<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasRoles;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasUsers;
use DigipolisGent\Domainator9k\CoreBundle\Tools\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="appenvironment")
 */
class AppEnvironment
{
    use HasUsers;
    use HasRoles;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Application
     * @ORM\ManyToOne(targetEntity="Application", cascade={"all"}, inversedBy="appEnvironments")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $application;

    /**
     * @var int
     * @ORM\Column(name="sock_app_id", type="integer", nullable=true)
     */
    protected $sockApplicationId;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(name="domains", type="simple_array", nullable=true)
     */
    protected $domains;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ciJobUri;

    /**
     * @var string
     * @ORM\Column(name="preferred_domain", type="string", length=255, nullable=true)
     */
    protected $preferredDomain;

    /**
     * @var string
     * @ORM\Column(name="git_ref", type="string", nullable=false, options={"default"="master"})
     */
    protected $gitRef = 'master';

    /**
     * @var string
     * @ORM\Column(name="site_config", type="text", nullable=true)
     */
    protected $siteConfig;

    /**
     * @var string
     * @ORM\Column(name="cron", type="text", nullable=true)
     */
    protected $cron;

    /**
     * @var DatabaseSettings
     * @ORM\OneToOne(targetEntity="DatabaseSettings", cascade={"all"}, mappedBy="appEnvironment")
     */
    protected $databaseSettings;

    /**
     * @var string
     * @ORM\Column(name="salt", type="string", length=255, nullable=false)
     */
    protected $salt;

    /**
     * @var \Datetime
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \Datetime
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $devPermissions;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $prod;

    /**
     * Creates a new AppEnvironment.
     *
     * @param Application $application
     * @param string $name
     * @param bool $devPermissions
     * @param bool $prod
     */
    public function __construct(Application $application, $name, $devPermissions, $prod)
    {
        $this->name = $name;
        $this->application = $application;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->salt = uniqid(mt_rand(), true);
        $this->users = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->cron = $application->getCron();

        $this->assertServerSettings();
        $this->assertDatabaseSettings();

        // Set default git branch.
        // @TODO: Get this from a key-value store.
        $this->gitRef = '';
        $this->devPermissions = $devPermissions;
        $this->prod = $prod;
    }

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
     * Gets the salt.
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Gets the application.
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Sets the application.
     *
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
     * Gets the sock application id.
     *
     * @return int|null
     */
    public function getSockApplicationId()
    {
        return $this->sockApplicationId;
    }

    /**
     * Sets the sock application id.
     *
     * @param int|null $sockApplicationId
     *
     * @return $this
     */
    public function setSockApplicationId($sockApplicationId)
    {
        $this->sockApplicationId = $sockApplicationId;

        return $this;
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
     * Gets the canonical name.
     *
     * @return string
     */
    public function getNameCanonical()
    {
        return StringHelper::canonicalize($this->getName());
    }

    /**
     * Gets the full canonical name.
     *
     * @return string
     */
    public function getFullNameCanonical()
    {
        return $this->getApplication()->getNameCanonical() . '_' . StringHelper::canonicalize($this->getName());
    }

    /**
     * Gets the domains.
     *
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * Sets the domains.
     *
     * @param array $domains
     *
     * @return $this
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;

        if (!in_array($this->preferredDomain, $domains)) {
            $this->preferredDomain = null;
        }

        return $this;
    }

    /**
     * Adds a domain.
     *
     * @param string $domain
     * @param bool   $isPreferred
     *
     * @return $this
     */
    public function addDomain($domain, $isPreferred = false)
    {
        $this->domains[] = $domain;

        if ($isPreferred) {
            $this->preferredDomain = $domain;
        }

        return $this;
    }

    /**
     * Removes a domain.
     *
     * @param string $domain
     *
     * @return $this
     */
    public function removeDomain($domain)
    {
        if ($this->domains) {
            foreach ($this->domains as $k => $d) {
                if ($d === $domain) {
                    unset($this->domains[$k]);

                    break;
                }
            }
        }

        if (!in_array($this->preferredDomain, $this->domains)) {
            $this->preferredDomain = null;
        }

        return $this;
    }

    /**
     * If no preferred domain is explicitly set, the first domain will be returned.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getPreferredDomain()
    {
        if (null === $this->preferredDomain) {
            if (!count($this->domains)) {
                throw new \Exception('no domains configured');
            }

            return reset($this->domains);
        }

        return $this->preferredDomain;
    }

    /**
     * Set the preferred domain.
     *
     * @param string $preferredDomain
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setPreferredDomain($preferredDomain)
    {
        if (!$this->domains || !in_array($preferredDomain, $this->domains)) {
            throw new \InvalidArgumentException(sprintf("the domain '%s' is not a configured domain for this environment.", $preferredDomain));
        }

        $this->preferredDomain = $preferredDomain;

        return $this;
    }

    /**
     * Add a default domain based on the environment.
     *
     * @param Environment $env
     * @param string $scheme
     *
     * @return $this
     */
    public function setDomainByDefault(Environment $env, $scheme)
    {
        $urlName = $this->getApplication()->getNameForUrl();

        $url = $env->getUrlStructure();

        $url = str_replace('[APP_NAME]', $urlName, $url);
        $url = str_replace('[URL_SCHEMA]', $scheme, $url);

        $this->addDomain($url);

        return $this;
    }

    /**
     * Gets the git ref.
     *
     * @return mixed
     */
    public function getGitRef()
    {
        return $this->gitRef;
    }

    /**
     * Sets the git ref.
     *
     * @param mixed $gitRef
     *
     * @return $this
     */
    public function setGitRef($gitRef)
    {
        $this->gitRef = $gitRef;

        return $this;
    }

    /**
     * Gets the site config.
     *
     * @return string
     */
    public function getSiteConfig()
    {
        // windows line endings fuckery
        return str_replace("\r", '', $this->siteConfig);
    }

    /**
     * Sets the site config.
     *
     * @param string $siteConfig
     *
     * @return $this
     */
    public function setSiteConfig($siteConfig)
    {
        $this->siteConfig = $siteConfig;

        return $this;
    }

    /**
     * Gets the cron job.
     *
     * @return string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * Sets the cron job.
     *
     * @param string $cron
     *
     * @return $this
     */
    public function setCron($cron)
    {
        $this->cron = $cron;

        return $this;
    }

    /**
     * Gets the created at time.
     *
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Gets the updated at time.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Gets the server settings;
     *
     * @return ServerSettings
     */
    public function getServerSettings()
    {
        $this->assertServerSettings();

        return $this->serverSettings;
    }

    /**
     * Asserts the server settings are set.
     *
     * @return $this
     */
    public function assertServerSettings()
    {
        // if we don't have any settings, create settings with defaults
        if (!$this->serverSettings) {
            $this->serverSettings = $this->createDefaultServerSettings();
        }

        return $this;
    }

    /**
     * Creates default server settings based on the application.
     *
     * @return \DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings
     */
    protected function createDefaultServerSettings()
    {
        if (null === $this->getApplication()->getParent()) {
            $user = substr($this->getApplication()->getNameCanonical(), 0, 14);
            $pass = uniqid('', false);

            return new ServerSettings(
              $this, $user, $pass
            );
        } else {
            $parentEnv = $this->getApplication()->getParent()->getAppEnvironment($this->getName());
            $user = $parentEnv->getServerSettings()->getUser();
            $pass = $parentEnv->getServerSettings()->getPassword();

            $serverSettings = new ServerSettings(
              $this, $user, $pass
            );

            $serverSettings->setSockAccountId($parentEnv->getServerSettings()->getSockAccountId());

            return $serverSettings;
        }
    }

    /**
     * Gets the database settings.
     *
     * @return DatabaseSettings|null
     */
    public function getDatabaseSettings()
    {
        $this->assertDatabaseSettings();

        return $this->databaseSettings;
    }

    /**
     * Asserts the database settings.
     *
     * @return $this
     */
    public function assertDatabaseSettings()
    {
        // if we don't have any settings, but the project is configured to use one
        // create settings with defaults
        if (!$this->databaseSettings && $this->getApplication()->hasDatabase()) {
            $this->databaseSettings = $this->createDefaultDatabaseSettings();
        }

        return $this;
    }

    /**
     * Creates the default database settings based on the application.
     *
     * @return \DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings
     */
    protected function createDefaultDatabaseSettings()
    {
        return new DatabaseSettings(
            $this,
            substr($this->getApplication()->getNameCanonical(), 0, 14) . '_' . substr($this->getNameCanonical(), 0, 1)
        );
    }

    /**
     * Sets the database settings.
     *
     * @param DatabaseSettings|null $databaseSettings
     *
     * @return $this
     */
    public function setDatabaseSettings(DatabaseSettings $databaseSettings = null)
    {
        $this->databaseSettings = $databaseSettings;

        return $this;
    }

    /**
     * Replaces config placeholders with their values.
     *
     * @param $content
     * @param array|Server[] $servers
     *
     * @throws \Exception
     *
     * @return string
     */     //TODO: move out of environment ..

    public function replaceConfigPlaceholders($content, array $servers = array())
    {
        $ip = '';
        foreach ($servers as $server) {
            if ($server->isTaskServer() && $server->getEnvironment() === $this->getNameCanonical()) {
                $ip = $server->getIp();

                break;
            }
        }

        return str_replace([
            '[[URL]]',
            '[[IP]]',
        ], [
            $this->getPreferredDomain(),
            $ip,
        ], $content);
    }

    /**
     * Gets project specific directories based on the user.
     *
     * @param string $user
     *
     * @return string[]
     */
    public function getProjectSpecificDirs($user)
    {
        return $this->getApplication()->getType()->getDirectories(
            $this->getApplication(),
            $user
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Gets the ci job uri.
     *
     * @return string
     */
    public function getCiJobUri()
    {
        return $this->ciJobUri;
    }

    /**
     * Sets the ci job uri.
     *
     * @param string $ciJobUri
     *
     * @return $this
     */
    public function setCiJobUri($ciJobUri)
    {
        $this->ciJobUri = $ciJobUri;

        return $this;
    }

    /**
     * Checks whether or not devs have permissions on this appenvironment.
     *
     * @return bool
     */
    public function isDevPermissions()
    {
        return $this->devPermissions;
    }

    /**
     * Sets whether or not devs have permissions on this appenvironment.
     *
     * @param bool $devPermissions
     *
     * @return $this
     */
    public function setDevPermissions($devPermissions)
    {
        $this->devPermissions = $devPermissions;

        return $this;
    }

    /**
     * Checks if this is the prod environment.
     *
     * @return bool
     */
    public function isProd()
    {
        return $this->prod;
    }

    /**
     * Sets whether or not this is the prod environment.
     * 
     * @param bool $prod
     *
     * @return $this
     */
    public function setProd($prod)
    {
        $this->prod = $prod;

        return $this;
    }
}

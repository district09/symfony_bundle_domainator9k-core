<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Ctrl\RadBundle\Entity\User;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasRoles;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasUsers;
use DigipolisGent\Domainator9k\CoreBundle\Tools\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DigipolisGent\Domainator9k\CoreBundle\Repository\ApplicationRepository")
 * @ORM\Table(name="application")
 */
class Application
{
    use HasUsers;
    use HasRoles;

    // @TODO: Move to key value store.
    const URL_SCHEME_STAD_GENT = 'stad.gent';
    const URL_SCHEME_GENT_BE = 'gent.be';
    const URL_SCHEME_OCMW_GENT = 'ocmw.gent';
    const URL_SCHEME_GENT_GRP = 'gentgrp.gent.be';
    const URL_SCHEME_GENT_GRP_ENV = 'env.gent.grp';
    const URL_SCHEME_FREE_FORM = 'free_form';

    protected $appTypeSettings; //temporary property needed, before persist & flush in a different table
    protected $ciAppTypeSettings; //temporary property needed, before persist & flush in a different table

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="255")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="name_canonical", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="255")
     */
    protected $nameCanonical;

    /**
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Application", mappedBy="parent")
     **/
    protected $children;

    /**
     * @var ApplicationTypeInterface
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(name="app_type_slug", type="string", nullable=false)
     */
    protected $appTypeSlug;

    /**
     * @var string
     * @ORM\Column(name="ci_type_slug", type="string", nullable=false)
     */
    protected $ciTypeSlug;

    /**
     * @var string
     * @ORM\Column(name="git_repo", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[1-9a-zA-Z_\-]+\/[1-9a-zA-Z_\-]+$/", message="Dit is geen geldige repository")
     */
    protected $gitRepo;

    /**
     * @var bool
     * @ORM\Column(name="has_database", type="boolean", options={"default"="1"})
     */
    protected $hasDatabase = true;

    /**
     * @var bool
     * @ORM\Column(name="has_solr", type="boolean", options={"default"="0"})
     */
    protected $hasSolr = false;

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
     * @var AppEnvironment[]|ArrayCollection|array
     * @ORM\OneToMany(targetEntity="AppEnvironment", mappedBy="application", cascade={"persist"}, orphanRemoval=true)
     */
    protected $appEnvironments;

    /**
     * @var Build
     * @ORM\OneToOne(targetEntity="Build")
     * @ORM\JoinColumn(name="provision_build_id", referencedColumnName="id", nullable=true)
     */
    protected $provisionBuild;

    /**
     * @var Build[]
     * @ORM\OneToMany(targetEntity="Build", mappedBy="application", cascade={"all"}, orphanRemoval=true)
     */
    protected $builds;

    /**
     * @var bool
     * @ORM\Column(name="dns_mail_sent", type="boolean", nullable=false, options={"default": "0"})
     */
    protected $dnsMailSent = false;

    /**
     * @var string|null
     * @ORM\Column(name="dns_template", type="text", nullable=true)
     */
    protected $dnsMailTemplate;

    /**
     * @var string|null
     * @ORM\Column(name="cron", type="text", nullable=true)
     */
    protected $cron;

    /**
     * @param ApplicationTypeInterface $type
     * @param string                   $name
     * @param Environment[]            $environments
     * @param string                   $urlScheme    for production
     * @param string|null              $siteConfig
     * @param Application|null         $parent
     */
    public function __construct(ApplicationTypeInterface $type, $name, $environments, $urlScheme = self::URL_SCHEME_STAD_GENT, $siteConfig = null, $parent = null)
    {
        $this->appTypeSlug = $type->getSlug();
        $this->name = $name;
        $this->nameCanonical = StringHelper::canonicalize($name);
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->appEnvironments = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->users = new ArrayCollection();

        $this->setParent($parent);

        foreach ($environments as $environment) {
            $appEnv = new AppEnvironment($this, $environment->getName(), $environment->isDevPermissions(), $environment->isProd(), $urlScheme);
            $appEnv->setDomainByDefault($environment, $urlScheme);
            $this->addAppEnvironment($appEnv);
        }

        foreach ($this->getAppEnvironments() as $e) {
            $e->setSiteConfig($siteConfig);
        }
    }

    /**
     * @return string
     */
    public function getAppTypeSlug()
    {
        if ($this->type) {
            return $this->getType()->getSlug();
        }

        return $this->appTypeSlug;
    }

    /**
     * @param string $appTypeSlug
     */
    public function setAppTypeSlug($appTypeSlug)
    {
        $this->appTypeSlug = $appTypeSlug;

        return $this;
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
     * @return string
     */
    public function getNameCanonical()
    {
        return $this->nameCanonical;
    }

    /**
     * @return string
     */
    public function getNameForUrl()
    {
        return StringHelper::canonicalize($this->getName(), true, true);
    }

    /**
     * @return string
     */
    public function getNameForFolder()
    {
        if (null === $this->getParent()) {
            return 'default';
        }

        return substr($this->getNameCanonical(), 0, 14);
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
     * Set parent.
     *
     * @param \DigipolisGent\Domainator9k\CoreBundle\Entity\Application $parent
     *
     * @return Application
     */
    public function setParent(Application $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \DigipolisGent\Domainator9k\CoreBundle\Entity\Application
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return array|ArrayCollection|Application[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return ApplicationTypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ApplicationTypeInterface $type
     *
     * @return $this
     */
    public function setType(ApplicationTypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getGitRepo()
    {
        return $this->gitRepo;
    }

    /**
     * @param string $gitRepo
     *
     * @return $this
     */
    public function setGitRepo($gitRepo)
    {
        $this->gitRepo = $gitRepo;

        return $this;
    }

    /**
     * @return string
     */
    public function getGitRepoFull()
    {
        // @TODO: Support multiple platforms.
        return 'git@bitbucket.org:' . $this->gitRepo . '.git';
    }

    /**
     * @return bool
     */
    public function hasDatabase()
    {
        return $this->hasDatabase;
    }

    /**
     * Define if the application uses a databases
     * If there are databases active, this function will ALWAYS set true.
     *
     * @param bool $hasDatabase converted to TRUE if $this->hasActiveDatabases is TRUE
     *
     * @return $this
     */
    public function setHasDatabase($hasDatabase)
    {
        // if there are active databases, the only valid setting is true
        if ($this->hasActiveDatabases()) {
            $hasDatabase = true;
        }

        $this->hasDatabase = $hasDatabase;

        // make sure we don't have any remaining database settings
        if (!$hasDatabase) {
            foreach ($this->appEnvironments as $env) {
                $env->setDatabaseSettings(null);
            }
        }
        // make sure we have database settings
        else {
            foreach ($this->appEnvironments as $env) {
                $env->assertDatabaseSettings();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasActiveDatabases()
    {
        foreach ($this->appEnvironments as $env) {
            if ($env->getDatabaseSettings() && $env->getDatabaseSettings()->isCreated()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasSolr()
    {
        return $this->hasSolr;
    }

    /**
     * @param bool $hasSolr
     *
     * @return $this
     */
    public function setHasSolr($hasSolr)
    {
        $this->hasSolr = $hasSolr;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param User|null $user filters out application not available for this user
     *
     * @return AppEnvironment[]
     */
    public function getAppEnvironments(User $user = null)
    {
        if ($user) {
            return $this->appEnvironments->filter(function (AppEnvironment $env) use ($user) {
                return 'test' === $env->getNameCanonical()
                    || $env->hasUser($user)
                    || $env->hasAnyRole($user->getRoles());
            });
        }

        return $this->appEnvironments;
    }

    /**
     * @param string|Environment $name
     *
     * @throws \Exception
     *
     * @return AppEnvironment
     */
    public function getAppEnvironment($name)
    {
        $env = null;
        if ($name instanceof Environment) {
            $env = $name;
            $name = (string) $name;
        }
        foreach ($this->appEnvironments as $e) {
            $matches = ($e->getName() === $name || $e->getNameCanonical() === $name)
                || ($env &&
                    ($e->getName() === $env->getName() || $e->getNameCanonical() === $env->getName())
                );
            if ($matches) {
                return $e;
            }
        }

        throw new \Exception(sprintf("Application has no environment '%s'", $name));
    }

    public function getProdAppEnvironment()
    {
        foreach ($this->appEnvironments as $e) {
            if ($e->isProd()) {
                return $e;
            }
        }

        throw new \Exception('Application has no environment marked as isProd == true');
    }

    /**
     * @param AppEnvironment $env
     *
     * @return $this
     */
    public function addAppEnvironment(AppEnvironment $env)
    {
        $this->appEnvironments->add($env);
        $env->setApplication($this);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProvisionBuild()
    {
        return $this->provisionBuild;
    }

    /**
     * @param mixed $provisionBuild
     *
     * @return $this
     */
    public function setProvisionBuild($provisionBuild)
    {
        $this->provisionBuild = $provisionBuild;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDnsMailSent()
    {
        return $this->dnsMailSent;
    }

    /**
     * @param bool $dnsMailSent
     *
     * @return $this
     */
    public function setDnsMailSent($dnsMailSent)
    {
        $this->dnsMailSent = $dnsMailSent;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDnsMailTemplate()
    {
        return $this->dnsMailTemplate;
    }

    /**
     * @param null|string $dnsMailTemplate
     *
     * @return $this
     */
    public function setDnsMailTemplate($dnsMailTemplate)
    {
        $this->dnsMailTemplate = $dnsMailTemplate;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * @param null|string $cron
     *
     * @return $this
     */
    public function setCron($cron)
    {
        $this->cron = $cron;

        return $this;
    }

    /**
     * If we have a previously successful build we should have enough config
     * to run partial builds.
     *
     * @return bool
     */
    public function allowPartialBuilds()
    {
        foreach ($this->builds as $b) {
            if ($b->isCompleted() && $b->getSuccess()) {
                return true;
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCiTypeSlug()
    {
        return $this->ciTypeSlug;
    }

    /**
     * @param string $ciTypeSlug
     */
    public function setCiTypeSlug($ciTypeSlug)
    {
        $this->ciTypeSlug = $ciTypeSlug;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppTypeSettings()
    {
        return $this->appTypeSettings;
    }

    /**
     * @param mixed $appTypeSettings
     */
    public function setAppTypeSettings($appTypeSettings)
    {
        $this->appTypeSettings = $appTypeSettings;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCiAppTypeSettings()
    {
        return $this->ciAppTypeSettings;
    }

    /**
     * @param mixed $ciAppTypeSettings
     */
    public function setCiAppTypeSettings($ciAppTypeSettings)
    {
        $this->ciAppTypeSettings = $ciAppTypeSettings;

        return $this;
    }
}

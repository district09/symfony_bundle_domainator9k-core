<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="environment")
 * @UniqueEntity(fields={"name"})
 */
class Environment implements TemplateInterface
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank();
     * @Assert\Regex(
     *     pattern="/^[a-z]+$/",
     *     match=true,
     *     message="Your name cannot contain a space"
     * )
     */
    protected $name;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $prod;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ApplicationEnvironment",mappedBy="environment",cascade={"remove"})
     */
    protected $applicationEnvironments;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ApplicationTypeEnvironment",mappedBy="environment",cascade={"remove"})
     */
    protected $applicationTypeEnvironments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="VirtualServer",mappedBy="environment")
     */
    protected $virtualServers;

    /**
     * @var string
     *
     * @ORM\Column(name="git_ref",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $gitRef;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority",type="integer",nullable=true)
     */
    protected $priority;

    /**
     * Creates a new environment.
     */
    public function __construct()
    {
        $this->applicationEnvironments = new ArrayCollection();
        $this->applicationTypeEnvironments = new ArrayCollection();
        $this->virtualServers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public static function getSettingImplementationName()
    {
        return 'environment';
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
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Checks whether or not this is a production environment.
     *
     * @return bool
     */
    public function isProd()
    {
        return $this->prod;
    }

    /**
     * Sets whether or not this is a production environment.
     *
     * @param bool $prod
     */
    public function setProd($prod)
    {
        $this->prod = $prod;

        return $this;
    }

    /**
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function addApplicationEnvironment(ApplicationEnvironment $applicationEnvironment)
    {
        $this->applicationEnvironments->add($applicationEnvironment);
    }

    /**
     * @return ArrayCollection
     */
    public function getApplicationEnvironments()
    {
        return $this->applicationEnvironments;
    }

    /**
     * @param ApplicationTypeEnvironment $applicationTypeEnvironment
     */
    public function addApplicationTypeEnvironment(ApplicationTypeEnvironment $applicationTypeEnvironment)
    {
        $this->applicationTypeEnvironments->add($applicationTypeEnvironment);
    }

    /**
     * @return ArrayCollection
     */
    public function getApplicationTypeEnvironments()
    {
        return $this->applicationTypeEnvironments;
    }

    /**
     * @param VirtualServer $server
     */
    public function addVirtualServer(VirtualServer $virtualServer)
    {
        $this->virtualServers->add($virtualServer);
        $virtualServer->setEnvironment($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getVirtualServers()
    {
        return $this->virtualServers;
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
    public function setGitRef(string $gitRef)
    {
        $this->gitRef = $gitRef;
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

    /**
     * @return array
     */
    public static function getTemplateReplacements(): array
    {
        return [
            'config(key)' => 'getConfig(key)',
            'gitRef()' => 'getGitRef()',
        ];
    }

    /**
     * @return int
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority = null)
    {
        $this->priority = $priority;
    }
}

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
class Environment
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank();
     */
    protected $name;

    /**
     * @var bool
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
     * @ORM\OneToMany(targetEntity="Server",mappedBy="environment")
     */
    protected $servers;

    /**
     * Creates a new environment.
     */
    public function __construct()
    {
        $this->applicationEnvironments = new ArrayCollection();
        $this->applicationTypeEnvironments = new ArrayCollection();
        $this->servers = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getApplicationTypeEnvironments()
    {
        return $this->applicationTypeEnvironments;
    }

    /**
     * @return ArrayCollection
     */
    public function getServers()
    {
        return $this->servers;
    }
}

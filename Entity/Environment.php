<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
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

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $devPermissions;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $urlStructure;

    /**
     * Creates a new environment.
     */
    public function __construct()
    {
        $this->prod = false;
        $this->urlStructure = '';
    }

    /**
     * Checks whether or not devs have permissions on this environment.
     *
     * @return bool
     */
    public function isDevPermissions()
    {
        return $this->devPermissions;
    }

    /**
     * Sets whether or not devs have permissions on this environment.
     * @param bool $devPermissions
     */
    public function setDevPermissions($devPermissions)
    {
        $this->devPermissions = $devPermissions;

        return $this;
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
     * Gets the url structure.
     *
     * @return string
     */
    public function getUrlStructure()
    {
        return $this->urlStructure;
    }

    /**
     * Sets the url structure.
     *
     * @param string $urlStructure
     */
    public function setUrlStructure($urlStructure)
    {
        $this->urlStructure = $urlStructure;

        return $this;
    }
}

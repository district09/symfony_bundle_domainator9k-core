<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="environment")
 */
class Environment
{
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

    public function __construct()
    {
        $this->prod = false;
        $this->urlStructure = '';
    }

    /**
     * @return bool
     */
    public function isDevPermissions()
    {
        return $this->devPermissions;
    }

    /**
     * @param bool $devPermissions
     */
    public function setDevPermissions($devPermissions)
    {
        $this->devPermissions = $devPermissions;

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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return bool
     */
    public function isProd()
    {
        return $this->prod;
    }

    /**
     * @param bool $prod
     */
    public function setProd($prod)
    {
        $this->prod = $prod;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlStructure()
    {
        return $this->urlStructure;
    }

    /**
     * @param string $urlStructure
     */
    public function setUrlStructure($urlStructure)
    {
        $this->urlStructure = $urlStructure;

        return $this;
    }
}

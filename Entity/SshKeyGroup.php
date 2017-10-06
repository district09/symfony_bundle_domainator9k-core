<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ssh_key_group")
 */
class SshKeyGroup
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $label;

    /**
     * @var SshKey[]|array|ArrayCollection
     * @ORM\ManyToMany(targetEntity="SshKey", mappedBy="groups")
     */
    protected $keys;

    /**
     * @var AppEnvironment[]|array|ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppEnvironment", mappedBy="sshKeyGroups" )
     */
    protected $appEnvironments;

    /**
     * @var Settings[]|array|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DigipolisGent\Domainator9k\CoreBundle\Entity\Settings", mappedBy="defaultSshKeyGroups")
     */
    protected $defaultSettings;

    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return array|SshKey[]|ArrayCollection
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @param array|SshKey[]|ArrayCollection $keys
     *
     * @return $this
     */
    public function setKeys($keys)
    {
        $this->keys = $keys;

        return $this;
    }
}

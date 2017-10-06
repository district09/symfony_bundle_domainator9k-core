<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ssh_key")
 */
class SshKey
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
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @var SshKeyGroup[]|array|ArrayCollection
     * @ORM\ManyToMany(targetEntity="SshKeyGroup", inversedBy="keys")
     */
    protected $groups;

    /**
     * @param string $name
     * @param string $content
     */
    public function __construct($name, $content)
    {
        $this->name = $name;
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return SshKeyGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param SshKeyGroup[] $groups
     *
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupsAsString()
    {
        $labels = array();
        foreach ($this->getGroups() as $g) {
            $labels[] = $g->getLabel();
        }

        sort($labels);

        return implode(', ', $labels);
    }
}

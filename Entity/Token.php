<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'token')]
#[ORM\Entity]
class Token
{
    use IdentifiableTrait;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/[a-z0-9_]/', message: 'Name can only contain lowercase letters, numbers and underscores')]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'value', type: 'text', nullable: false)]
    #[Assert\NotBlank]
    protected $value;

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name.
     *
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
     * Gets the value.
     *
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Sets the value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}

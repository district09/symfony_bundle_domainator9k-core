<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

/**
 * Trait IdentifiableTrait
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity\Traits
 */
trait IdentifiableTrait
{

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    protected $id;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}

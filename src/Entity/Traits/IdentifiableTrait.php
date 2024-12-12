<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait IdentifiableTrait
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity\Traits
 */
trait IdentifiableTrait
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}

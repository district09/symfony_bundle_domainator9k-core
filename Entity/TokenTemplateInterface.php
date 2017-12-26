<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;


/**
 * Interface TokenTemplateInterface
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 */
interface TokenTemplateInterface
{
    public static function getTokenReplacements(): array;
}
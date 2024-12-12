<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

/**
 * Interface TemplateInterface
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 */
interface TemplateInterface
{
    public static function getTemplateReplacements(int $maxDepth = 3, array $skip = []): array;
}

<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Provider;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\SettingBundle\Provider\EntityTypeProviderInterface;

/**
 * Class EntityTypeProvider
 * @package DigipolisGent\Domainator9k\CoreBundle\Provider
 */
class EntityTypeProvider implements EntityTypeProviderInterface
{

    /**
     * @return array
     */
    public function getEntityTypes()
    {
        return [
            [
                'name' => 'environment',
                'class' => Environment::class,
            ]
        ];
    }
}
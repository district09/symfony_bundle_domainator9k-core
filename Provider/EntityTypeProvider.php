<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Provider;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\SettingBundle\Provider\EntityTypeProviderInterface;

class EntityTypeProvider implements EntityTypeProviderInterface
{
    /**
     * @return array
     */
    public function getEntityTypes()
    {
        return [
            'environment' => Environment::class,
            'server' => Server::class,
        ];
    }
}
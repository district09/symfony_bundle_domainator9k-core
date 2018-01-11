<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Provider;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
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
            'application' => AbstractApplication::class,
            'application_environment' => ApplicationEnvironment::class,
            'application_type_environment' => ApplicationTypeEnvironment::class,
            'application_type' => ApplicationType::class,
        ];
    }
}
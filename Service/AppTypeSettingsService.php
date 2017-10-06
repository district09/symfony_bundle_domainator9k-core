<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Registry;

class AppTypeSettingsService
{
    /**
     * @var Registry
     */
    private $doctrine;
    /**
     * @var ApplicationTypeBuilder
     */
    private $applicationTypeBuilder;

    /**
     * CiSettingsService constructor.
     *
     * @param Registry               $doctrine
     * @param ApplicationTypeBuilder $applicationTypeBuilder
     */
    public function __construct(Registry $doctrine, ApplicationTypeBuilder $applicationTypeBuilder)
    {
        $this->doctrine = $doctrine;
        $this->applicationTypeBuilder = $applicationTypeBuilder;
    }

    public function getSettings(Application $application, $fallback = true)
    {
        $type = $this->applicationTypeBuilder->getType($application->getAppTypeSlug());
        $settings = $this->doctrine->getManager()->getRepository($type->getSettingsEntityClass())->findOneBy(['application' => $application]);

        if (!$settings) {
            if (!$fallback) {
                return false;
            }
            $className = $type->getSettingsEntityClass();

            return new $className();
        }

        return $settings;
    }

    public function saveSettings($settings)
    {
        $this->doctrine->getManager()->persist($settings);
        $this->doctrine->getManager()->flush();
    }
}

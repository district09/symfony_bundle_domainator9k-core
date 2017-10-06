<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Registry;

class CiAppTypeSettingsService
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
     * @var CiTypeBuilder
     */
    private $ciTypeBuilder;

    /**
     * CiSettingsService constructor.
     *
     * @param Registry               $doctrine
     * @param ApplicationTypeBuilder $applicationTypeBuilder
     * @param CiTypeBuilder          $ciTypeBuilder
     */
    public function __construct(Registry $doctrine, ApplicationTypeBuilder $applicationTypeBuilder, CiTypeBuilder $ciTypeBuilder)
    {
        $this->doctrine = $doctrine;
        $this->applicationTypeBuilder = $applicationTypeBuilder;
        $this->ciTypeBuilder = $ciTypeBuilder;
    }

    /**
     * @param CiTypeInterface $ciType
     * @param BaseAppType     $appType
     *
     * @return mixed
     */
    public function getSettings(CiTypeInterface $ciType, BaseAppType $appType)
    {
        $settings = $this->doctrine->getManager()->getRepository($ciType->getAppTypeSettingsEntityClass())->findOneBy(['appTypeSlug' => $appType->getSlug(), 'ciTypeSlug' => $ciType->getSlug()]);
        if (!$settings) {
            $className = $ciType->getAppTypeSettingsEntityClass();
            $class = new $className($ciType->getSlug(), $appType->getSlug());
            /* @noinspection PhpUndefinedMethodInspection */
            $class->setAdditionalConfig($ciType->getAdditionalConfig());

            return $class;
        }

        $settings->setAdditionalConfig($ciType->getAdditionalConfig());

        return $settings;
    }

    public function getSettingsForApp(Application $app)
    {
        if ($app->getCiAppTypeSettings()) {
            return $app->getCiAppTypeSettings();
        }

        $ciType = $this->ciTypeBuilder->getType($app->getCiTypeSlug());
        $appType = $this->applicationTypeBuilder->getType($app->getAppTypeSlug());

        $ciAppTypeSettings = $this->doctrine->getManager()->getRepository($ciType->getAppTypeSettingsEntityClass())->findOneBy(['appId' => $app->getId()]);
        if ($ciAppTypeSettings) {
            return $ciAppTypeSettings;
        }

        return $this->getSettings($ciType, $appType);
    }

    public function saveSettings($settings)
    {
        $this->doctrine->getManager()->persist($settings);
        $this->doctrine->getManager()->flush();
    }
}

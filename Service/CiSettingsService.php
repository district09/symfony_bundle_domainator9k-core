<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class CiSettingsService
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * CiSettingsService constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param CiTypeInterface $deployType
     *
     * @return mixed
     */
    public function getSettings(CiTypeInterface $deployType) //todo:settingsinterface for return
    {
        $settings = $this->doctrine->getManager()->getRepository($deployType->getSettingsEntityClass())->findAll();
        if (!$settings || 0 === count($settings)) {
            $className = $deployType->getSettingsEntityClass();

            return new $className();
        }

        return $settings[0];
    }

    public function saveSettings($settings)
    {
        $this->doctrine->getManager()->persist($settings);
        $this->doctrine->getManager()->flush();
    }
}

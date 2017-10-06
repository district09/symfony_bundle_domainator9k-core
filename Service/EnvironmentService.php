<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;

class EnvironmentService
{
    /**
     * @var Registry
     */
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return Environment[]
     */
    public function getEnvironments()
    {
        $envs = $this->doctrine->getManager()->getRepository('DigipCoreBundle:Environment')->findAll();

        return $envs;
    }

    public function getEnvironmentChoices()
    {
        $envs = $this->getEnvironments();

        $arr = new ArrayCollection();

        foreach ($envs as $item) {
            $name = $item->getName();
            $arr[$name] = $name;
        }

        return $arr;
    }
}

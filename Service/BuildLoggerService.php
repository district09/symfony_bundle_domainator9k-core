<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class BuildLoggerService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class BuildLoggerService
{

    private $entityManager;
    private $build;

    /**
     * BuildLoggerService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Build $build
     */
    public function setBuild(Build $build)
    {
        $this->build = $build;
    }

    /**
     * @param string $line
     */
    public function addLine(string $line)
    {
        $log = $this->build->getLog();
        $log .= $line . PHP_EOL;

        $this->build->setLog($log);
        $this->entityManager->persist($this->build);
        $this->entityManager->flush();
    }

}
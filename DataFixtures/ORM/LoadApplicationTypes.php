<?php


namespace DigipolisGent\Domainator9k\CoreBundle\DataFixtures\ORM;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LoadApplicationTypes
 * @package DigipolisGent\Domainator9k\CoreBundle\DataFixtures\ORM
 */
class LoadApplicationTypes extends Fixture
{

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $applicationTypeRepository = $manager->getRepository(ApplicationType::class);
        $metadata = $manager->getMetadataFactory()->getMetadataFor(AbstractApplication::class);

        $environments = $manager->getRepository(Environment::class)->findAll();
        $applicationTypeEnvironmentRepository = $manager->getRepository(ApplicationTypeEnvironment::class);

        /** @var AbstractApplication $class */
        foreach ($metadata->subClasses as $class) {
            $name = $class::getApplicationType();
            $applicationType = $applicationTypeRepository->findOneBy(['name' => $name]);
            if (is_null($applicationType)) {
                $applicationType = new ApplicationType();
                $applicationType->setName($name);
                $manager->persist($applicationType);
            }

            foreach ($environments as $environment) {
                $criteria = [
                    'applicationType' => $applicationType,
                    'environment' => $environment,
                ];

                $applicationTypeEnvironment = $applicationTypeEnvironmentRepository->findOneBy($criteria);

                if (!$applicationTypeEnvironment) {
                    $applicationTypeEnvironment = new ApplicationTypeEnvironment();
                    $applicationTypeEnvironment->setApplicationType($applicationType);
                    $applicationTypeEnvironment->setEnvironment($environment);

                    $manager->persist($applicationTypeEnvironment);
                }
            }

        }

        $manager->flush();
    }
}

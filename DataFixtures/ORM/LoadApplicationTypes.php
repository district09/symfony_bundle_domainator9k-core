<?php


namespace DigipolisGent\Domainator9k\CoreBundle\DataFixtures\ORM;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\SettingBundle\Entity\SettingDataType;
use DigipolisGent\SettingBundle\Entity\SettingEntityType;
use DigipolisGent\SettingBundle\Service\DataTypeCollector;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

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

        foreach ($metadata->subClasses as $class){
            $type = $class::getType();
            $applicationType = $applicationTypeRepository->findOneBy(['type' => $type]);
            if(is_null($applicationType)){
                $applicationType = new ApplicationType();
                $applicationType->setType($type);
                $manager->persist($applicationType);
            }

            foreach ($environments as $environment){
                $criteria = [
                    'applicationType' => $applicationType,
                    'environment' => $environment,
                ];

                $applicationTypeEnvironment = $applicationTypeEnvironmentRepository->findOneBy($criteria);

                if(!$applicationTypeEnvironment){
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
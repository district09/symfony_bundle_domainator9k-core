<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;


use DigipolisGent\Domainator9k\CoreBundle\Entity\TokenTemplateInterface;
use DigipolisGent\SettingBundle\Entity\SettingEntityType;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use DigipolisGent\SettingBundle\Service\DataValueService;
use DigipolisGent\SettingBundle\Service\EntityTypeCollector;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TemplateService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TemplateService
{

    private $entityTypeCollector;
    private $entityManager;
    private $dataValueService;

    /**
     * TemplateService constructor.
     * @param EntityTypeCollector $entityTypeCollector
     * @param EntityManagerInterface $entityManager
     * @param DataValueService $dataValueService
     */
    public function __construct(
        EntityTypeCollector $entityTypeCollector,
        EntityManagerInterface $entityManager,
        DataValueService $dataValueService
    ) {
        $this->entityTypeCollector = $entityTypeCollector;
        $this->entityManager = $entityManager;
        $this->dataValueService = $dataValueService;
    }

    /**
     * @param string $className
     * @return array
     */
    public function getKeys(string $className): array
    {
        if (!class_exists($className)) {
            return [];
        }

        $keys = [];

        if (in_array(TokenTemplateInterface::class, class_implements($className))) {
            $tokenReplacementKeys = array_keys($className::getTokenReplacements());
            foreach ($tokenReplacementKeys as $tokenReplacementKey) {
                $keys[] = 'token:' . $tokenReplacementKey;
            }
        }

        if (in_array(SettingImplementationTrait::class, class_uses($className))) {
            $entityTypeName = $this->entityTypeCollector->getEntityTypeByClass($className);
            $entityTypeRepository = $this->entityManager->getRepository(SettingEntityType::class);
            $entityType = $entityTypeRepository->findOneBy(['name' => $entityTypeName]);

            foreach ($entityType->getSettingDataTypes() as $settingDataType) {
                $keys[] = 'config:' . $settingDataType->getKey();
            }
        }

        return $keys;
    }

    /**
     * @param string $text
     * @param array $entities
     * @return string
     */
    public function replaceKeys(string $text, array $entities = array()): string
    {
        foreach ($entities as $entityKey => $entity) {

            if (in_array(TokenTemplateInterface::class, class_implements($entity))) {
                $tokenReplacements = $entity::getTokenReplacements();

                foreach ($tokenReplacements as $tokenReplacementKey => $tokenReplacementValue) {
                    $key = '[[ ' . $entityKey . ':token:' . $tokenReplacementKey . ' ]]';

                    if (strpos($text, $key) !== false) {
                        $methods = explode('.', $tokenReplacementValue);
                        $value = $entity;
                        foreach ($methods as $method) {
                            $value = $value->{$method}();
                        }

                        $text = str_replace($key, $value, $text);
                    }
                }
            }

            if (in_array(SettingImplementationTrait::class, class_uses($entity))) {
                $entityTypeName = $this->entityTypeCollector->getEntityTypeByClass(get_class($entity));
                $entityTypeRepository = $this->entityManager->getRepository(SettingEntityType::class);
                $entityType = $entityTypeRepository->findOneBy(['name' => $entityTypeName]);

                foreach ($entityType->getSettingDataTypes() as $settingDataType) {
                    $key = '[[ ' . $entityKey . ':config:' . $settingDataType->getKey() . ' ]]';
                    if (strpos($text, $key) !== false) {
                        $value = $this->dataValueService->getValue($entity,$settingDataType->getKey());
                        $text = str_replace($key, $value, $text);
                    }
                }

            }
        }

        return $text;
    }

}
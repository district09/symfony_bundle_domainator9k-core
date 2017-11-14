<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeSettingsInterface;
use LogicException;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

abstract class BaseCiType implements CiTypeInterface
{
    protected $slug; //i getter
    protected $name; //i getter
    protected $ymlConfigName; //i getter
    protected $additionalConfig; //i getter
    protected $settingsFormClass; //i getter
    protected $settingsEntityClass; //i getter
    protected $appTypeSettingsFormClass; //i getter
    protected $appTypeSettingsEntityClass; //i getter
    protected $processorServiceClass; //i getter
    //which settings field should be used to build the menu of this citype // i dont like this, will change
    protected $menuUrlFieldName = 'url'; //i getter

    /**
     * {@inheritdoc}
     */
    public function buildCiUrl(CiTypeSettingsInterface $settings, AppEnvironment $env)
    {
        return rtrim($settings->getUrl(), '/') . '/job/' . $settings->getJobName($env);
    }

    /**
     * {@inheritdoc}
     */
    public function buildUrl(CiTypeSettingsInterface $ciTypeSettings)
    {
        return $ciTypeSettings->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuUrlFieldName()
    {
        return $this->menuUrlFieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function parseYamlConfig()
    {
        $class_info = new ReflectionClass($this);
        if ($this->ymlConfigName) {
            //todo: parse @ notation or something
            $path = dirname($class_info->getFileName()) . '/' . $this->ymlConfigName;
        } else {
            $path = dirname($class_info->getFileName()) . '/type_config.yml';
        }
        $content = Yaml::parse(file_get_contents($path));
        $this->mapYmlToProperties($content);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapYmlToProperties($content)
    {
        $this->additionalConfig = $content['additional_config'];
        $this->name = $content['name'];
        $this->slug = $content['slug'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormClass()
    {
        if (!$this->settingsFormClass) {
            throw new LogicException('SettingsFormClass in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->settingsFormClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityClass()
    {
        if (!$this->settingsEntityClass) {
            throw new LogicException('SettingsEntityClass in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->settingsEntityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessorServiceClass()
    {
        if (!$this->processorServiceClass) {
            throw new LogicException('ProcessorServiceClass in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->processorServiceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppTypeSettingsFormClass()
    {
        if (!$this->appTypeSettingsFormClass) {
            throw new LogicException('AppTypeSettingsFormClass in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->appTypeSettingsFormClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppTypeSettingsEntityClass()
    {
        if (!$this->appTypeSettingsEntityClass) {
            throw new LogicException('AppTypeSettingsEntityClass in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->appTypeSettingsEntityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (!$this->name) {
            throw new LogicException('Name in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        if (!$this->slug) {
            throw new LogicException('Slug in CiType ' . get_class($this) . ' cannot be false');
        }

        return $this->slug;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalConfig()
    {
        return $this->additionalConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getYmlConfigName()
    {
        return $this->ymlConfigName;
    }
}

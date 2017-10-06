<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
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

    //todo settingsinterface?
    public function buildCiUrl($settings, AppEnvironment $env)
    {
        return $settings->getUrl().'job/'.$env->getFullNameCanonical();
    }

    public function buildUrl($ciTypeSettings)
    {
        return $ciTypeSettings->getUrl();
    }

    /**
     * @return string
     */
    public function getMenuUrlFieldName()
    {
        return $this->menuUrlFieldName;
    }

    // todo, refactor, also used in basedeploytype
    public function parseYamlConfig()
    {
        $class_info = new ReflectionClass($this);
        if ($this->ymlConfigName) {
            //todo: parse @ notation or something
            $path = $class_info->getFileName().'../../'.$this->ymlConfigName;
        } else {
            $path = $class_info->getFileName().'../../type_config.yml';
        }
        $content = Yaml::parse(file_get_contents($path));
        $this->mapYmlToProperties($content);
    }

    protected function mapYmlToProperties($content)
    {
        $this->additionalConfig = $content['additional_config'];
        $this->name = $content['name'];
        $this->slug = $content['slug'];
    }

    /**
     * @return mixed
     */
    public function getSettingsFormClass()
    {
        if (!$this->settingsFormClass) {
            throw new \LogicException('SettingsFormClass in CiType '.get_class($this).' cannot be false');
        }

        return $this->settingsFormClass;
    }

    /**
     * @return mixed
     */
    public function getSettingsEntityClass()
    {
        if (!$this->settingsEntityClass) {
            throw new \LogicException('SettingsEntityClass in CiType '.get_class($this).' cannot be false');
        }

        return $this->settingsEntityClass;
    }

    /**
     * @return mixed
     */
    public function getProcessorServiceClass()
    {
        if (!$this->processorServiceClass) {
            throw new \LogicException('ProcessorServiceClass in CiType '.get_class($this).' cannot be false');
        }

        return $this->processorServiceClass;
    }

    /**
     * @return mixed
     */
    public function getAppTypeSettingsFormClass()
    {
        if (!$this->appTypeSettingsFormClass) {
            throw new \LogicException('AppTypeSettingsFormClass in CiType '.get_class($this).' cannot be false');
        }

        return $this->appTypeSettingsFormClass;
    }

    /**
     * @return mixed
     */
    public function getAppTypeSettingsEntityClass()
    {
        if (!$this->appTypeSettingsEntityClass) {
            throw new \LogicException('AppTypeSettingsEntityClass in CiType '.get_class($this).' cannot be false');
        }

        return $this->appTypeSettingsEntityClass;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if (!$this->slug) {
            throw new \LogicException('Name in CiType '.get_class($this).' cannot be false');
        }

        return $this->name;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        if (!$this->slug) {
            throw new \LogicException('Slug in CiType '.get_class($this).' cannot be false');
        }

        return $this->slug;
    }

    /**
     * @return mixed
     */
    public function getAdditionalConfig()
    {
        return $this->additionalConfig;
    }

    /**
     * @return null|string
     */
    public function getYmlConfigName()
    {
        return $this->ymlConfigName;
    }
}

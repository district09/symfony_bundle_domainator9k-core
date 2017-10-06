<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\AppTypeSettingsService;
use DigipolisGent\Domainator9k\CoreBundle\Service\EnvironmentService;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

abstract class BaseAppType implements ApplicationTypeInterface
{
    protected $slug = '';
    protected $name;
    protected $publicFolder;
    protected $directories = [];
    protected $cron;
    protected $databaseRequired;
    protected $ymlConfigName;
    protected $additionalConfig;

    protected $settingsFormClass;
    protected $settingsEntityClass;

    /**
     * @var AppTypeSettingsService
     */
    protected $appTypeSettingsService;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    /**
     * @param EnvironmentService $environmentService
     */
    public function setEnvironmentService($environmentService)
    {
        $this->environmentService = $environmentService;
    }

    /**
     * @return EnvironmentService
     */
    public function getEnvironmentService()
    {
        return $this->environmentService;
    }

    /**
     * @param mixed $appTypeSettingsService
     */
    public function setAppTypeSettingsService($appTypeSettingsService)
    {
        $this->appTypeSettingsService = $appTypeSettingsService;
    }

    /**
     * @return AppTypeSettingsService
     */
    public function getAppTypeSettingsService()
    {
        return $this->appTypeSettingsService;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * @return mixed
     */
    public function getPublicFolder()
    {
        return $this->publicFolder;
    }

    /**
     * @return mixed
     */
    public function isDatabaseRequired()
    {
        return $this->databaseRequired;
    }

    /**
     * @return mixed
     */
    public function getSiteConfig()
    {
        return $this->additionalConfig;
    }

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
        $this->cron = $content['cron'];
        $this->directories = $content['directories']; //todo: check what this is
        $this->publicFolder = $content['public_folder'];
        $this->databaseRequired = $content['database_required'];
    }

    public function getDirectories($user)
    {
        $directories = [];
        foreach ($this->directories as $directory) {
            $directories[] = str_replace('[[user]]', $user, $directory);
        }

        return $directories;
    }

    /**
     * @return string
     */
    public function getSettingsFormClass()
    {
        return $this->settingsFormClass;
    }

    /**
     * @return string
     */
    public function getSettingsEntityClass()
    {
        return $this->settingsEntityClass;
    }
}

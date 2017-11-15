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
    protected $siteConfig;

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
     * Sets the environment service.
     *
     * @param EnvironmentService $environmentService
     */
    public function setEnvironmentService(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;

        return $this;
    }

    /**
     * Gets the environment service.
     *
     * @return EnvironmentService
     */
    public function getEnvironmentService()
    {
        return $this->environmentService;
    }

    /**
     * Sets the app type settings service.
     *
     * @param mixed $appTypeSettingsService
     */
    public function setAppTypeSettingsService(AppTypeSettingsService $appTypeSettingsService)
    {
        $this->appTypeSettingsService = $appTypeSettingsService;

        return $this;
    }

    /**
     * Gets the app type settings service.
     *
     * @return AppTypeSettingsService
     */
    public function getAppTypeSettingsService()
    {
        return $this->appTypeSettingsService;
    }

    /**
     * Gets the name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Gets the cronjob.
     *
     * @return string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * Gets the public folder.
     *
     * @return mixed
     */
    public function getPublicFolder()
    {
        return $this->publicFolder;
    }

    /**
     * Checks whether or not this app type requires a database.
     *
     * @return mixed
     */
    public function isDatabaseRequired()
    {
        return $this->databaseRequired;
    }

    /**
     * Gets the site config.
     *
     * @return mixed
     */
    public function getSiteConfig()
    {
        return $this->siteConfig;
    }

    /**
     * Parses the YAML config.
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
     * Maps the YAML contents to properties.
     *
     * @param array $content
     */
    protected function mapYmlToProperties($content)
    {
        $this->siteConfig = $content['additional_config'];
        $this->name = $content['name'];
        $this->slug = $content['slug'];
        $this->cron = $content['cron'];
        $this->directories = $content['directories']; //todo: check what this is
        $this->publicFolder = $content['public_folder'];
        $this->databaseRequired = $content['database_required'];
    }

    /**
     * Gets the directories for this apptype.
     *
     * @param string $user
     *
     * @return array
     */
    public function getDirectories($user)
    {
        $directories = [];
        foreach ($this->directories as $directory) {
            $directories[] = str_replace('[[user]]', $user, $directory);
        }

        return $directories;
    }

    /**
     * Gets the settings form class.
     *
     * @return string
     */
    public function getSettingsFormClass()
    {
        return $this->settingsFormClass;
    }

    /**
     * Gets the settings entity class.
     *
     * @return string
     */
    public function getSettingsEntityClass()
    {
        return $this->settingsEntityClass;
    }
}

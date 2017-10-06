<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="application_type")
 */
class ApplicationType implements ApplicationTypeInterface
{
    const TYPE_GENERIC = 'generic';
    const TYPE_DRUPAL_7 = 'drupal_7';
    const TYPE_DRUPAL_8 = 'drupal_8';
    const TYPE_SYMFONY_2 = 'symfony_2';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="slug", type="string", nullable=false)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="public_folder", type="string", nullable=true)
     */
    protected $publicFolder = '';

    /**
     * @var string
     * @ORM\Column(name="site_config", type="text", nullable=true)
     */
    protected $siteConfig;

    /**
     * @var string
     * @ORM\Column(name="cron", type="text", nullable=true)
     */
    protected $cron;

    /**
     * @var Application[]
     * @ORM\OneToMany(targetEntity="Application", mappedBy="type", orphanRemoval=false)
     */
    protected $applications;

    //backwards compat with old application types
    public function getConfigFiles(AppEnvironment $env, array $servers, Settings $settings)
    {
        return $env->getConfigFiles($env->getServerSettings()->getUser(), $servers, $settings);
    }

    public function isDatabaseRequired()
    {
        return in_array($this->getSlug(), array(self::TYPE_DRUPAL_7, self::TYPE_SYMFONY_2));
    }

    ////////////

    /**
     * protected constructor, creating new application types is not allowed.
     *
     * @param string $slug
     */
    protected function __construct($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicFolder()
    {
        return $this->publicFolder;
    }

    /**
     * @param string $publicFolder
     *
     * @return $this
     */
    public function setPublicFolder($publicFolder)
    {
        $this->publicFolder = $publicFolder;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteConfig()
    {
        return $this->siteConfig;
    }

    /**
     * @param string $siteConfig
     *
     * @return $this
     */
    public function setSiteConfig($siteConfig)
    {
        $this->siteConfig = $siteConfig;

        return $this;
    }

    /**
     * @return string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * @param string $cron
     *
     * @return $this
     */
    public function setCron($cron)
    {
        $this->cron = $cron;

        return $this;
    }

    /**
     * @param string $user username on the server
     *
     * @return array
     */
    public function getDirectories($user)
    {
        switch ($this->getSlug()) {
            case self::TYPE_DRUPAL_7:
                return array(
                    "/home/$user/.drush",
                );
            case self::TYPE_DRUPAL_8:
                return array(
                    "/home/$user/.drush",
                );
        }

        return array();
    }

    public function __toString()
    {
        return $this->name;
    }
}

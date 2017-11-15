<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class BaseCiAppTypeSettings
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    protected $additionalConfig;

    /**
     * @var string
     * @ORM\Column(name="app_type_slug", type="string", nullable=false)
     */
    protected $appTypeSlug;

    /**
     * @var string
     * @ORM\Column(name="ci_type_slug", type="string", nullable=false)
     */
    protected $ciTypeSlug;

    /**
     * @var string
     * @ORM\Column(name="app_id", type="integer", nullable=true)
     */
    protected $appId;

    /**
     * BaseCiAppTypeSettings constructor.
     *
     * @param string $appTypeSlug
     * @param string $ciTypeSlug
     */
    public function __construct($ciTypeSlug, $appTypeSlug)
    {
        $this->appTypeSlug = $appTypeSlug;
        $this->ciTypeSlug = $ciTypeSlug;
    }

    /**
     * Gets the app type slug.
     *
     * @return mixed
     */
    public function getAppTypeSlug()
    {
        return $this->appTypeSlug;
    }

    /**
     * Sets the app type slug.
     *
     * @param mixed $appTypeSlug
     */
    public function setAppTypeSlug($appTypeSlug)
    {
        $this->appTypeSlug = $appTypeSlug;

        return $this;
    }

    /**
     * Gets the ci type slug.
     *
     * @return string
     */
    public function getCiTypeSlug()
    {
        return $this->ciTypeSlug;
    }

    /**
     * Sets the ci type slug.
     *
     * @param string $ciTypeSlug
     */
    public function setCiTypeSlug($ciTypeSlug)
    {
        $this->ciTypeSlug = $ciTypeSlug;

        return $this;
    }

    /**
     * Checks if this is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets whether or not this is enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Gets additional config.
     *
     * @return mixed
     */
    public function getAdditionalConfig()
    {
        return $this->additionalConfig;
    }

    /**
     * Sets additional config.
     *
     * @param mixed $additionalConfig
     */
    public function setAdditionalConfig($additionalConfig)
    {
        $this->additionalConfig = $additionalConfig;

        return $this;
    }

    /**
     * Gets the app id.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Sets the app id.
     * 
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }
}

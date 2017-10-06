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
     * @return mixed
     */
    public function getAppTypeSlug()
    {
        return $this->appTypeSlug;
    }

    /**
     * @param mixed $appTypeSlug
     */
    public function setAppTypeSlug($appTypeSlug)
    {
        $this->appTypeSlug = $appTypeSlug;
    }

    /**
     * @return string
     */
    public function getCiTypeSlug()
    {
        return $this->ciTypeSlug;
    }

    /**
     * @param string $ciTypeSlug
     */
    public function setCiTypeSlug($ciTypeSlug)
    {
        $this->ciTypeSlug = $ciTypeSlug;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getAdditionalConfig()
    {
        return $this->additionalConfig;
    }

    /**
     * @param mixed $additionalConfig
     */
    public function setAdditionalConfig($additionalConfig)
    {
        $this->additionalConfig = $additionalConfig;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }
}

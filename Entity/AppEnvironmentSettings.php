<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="settings_environment")
 */
class AppEnvironmentSettings
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="database_host", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Ip
     */
    protected $databaseHost = 'localhost';

    /**
     * @var string
     * @ORM\Column(name="redis_password", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $redisPassword = '';

    /**
     * @var Settings
     * @ORM\ManyToOne(targetEntity="DigipolisGent\Domainator9k\CoreBundle\Entity\Settings", inversedBy="appEnvironmentSettings")
     */
    protected $settings;

    /**
     * @var string
     * @ORM\Column(name="environment", type="string", length=10, nullable=false)
     */
    protected $environment;

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
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * @param string $databaseHost
     *
     * @return $this
     */
    public function setDatabaseHost($databaseHost)
    {
        $this->databaseHost = $databaseHost;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedisPassword()
    {
        return $this->redisPassword;
    }

    /**
     * @param string $redisPassword
     *
     * @return $this
     */
    public function setRedisPassword($redisPassword)
    {
        $this->redisPassword = $redisPassword;

        return $this;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param Settings $settings
     *
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }
}

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
     * Gets the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the database host.
     *
     * @return string
     */
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * Sets the database host.
     *
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
     * Gets the redis password.
     *
     * @return string
     */
    public function getRedisPassword()
    {
        return $this->redisPassword;
    }

    /**
     * Sets the redis password.
     *
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
     * Gets the settings.
     *
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets the settings.
     *
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
     * Gets the environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Sets the environment.
     * 
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

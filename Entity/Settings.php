<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="settings")
 */
class Settings
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
     * @ORM\Column(name="default_sock_ssh_keys", type="string", nullable=true)
     */
    protected $defaultSockSshKeys;

    /**
     * @var string
     * @ORM\Column(name="dns_mail_recipients", type="string", nullable=true)
     */
    protected $dnsMailRecipients;

    /**
     * @var string
     * @ORM\Column(name="dns_mail_template", type="text", nullable=false)
     */
    protected $dnsMailTemplate;

    /**
     * @var string
     * @ORM\Column(name="sock_domain", type="string", nullable=false)
     */
    protected $sockDomain;

    /**
     * @var string
     * @ORM\Column(name="sock_user_token", type="string", nullable=false)
     */
    protected $sockUserToken;

    /**
     * @var string
     * @ORM\Column(name="sock_client_token", type="string", nullable=false)
     */
    protected $sockClientToken;

    /**
     * @var AppEnvironmentSettings[]
     * @ORM\OneToMany(targetEntity="DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironmentSettings", mappedBy="settings")
     */
    protected $appEnvironmentSettings;

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
     * Sets the id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the default sock ssh keys.
     *
     * @return string
     */
    public function getDefaultSockSshKeys()
    {
        return $this->defaultSockSshKeys;
    }

    /**
     * Sets the default sock ssh keys.
     *
     * @param string $defaultSockSshKeys
     *
     * @return $this
     */
    public function setDefaultSockSshKeys($defaultSockSshKeys)
    {
        $this->defaultSockSshKeys = $defaultSockSshKeys;

        return $this;
    }

    /**
     * Gets the DNS mail recipients.
     *
     * @return string
     */
    public function getDnsMailRecipients()
    {
        return $this->dnsMailRecipients;
    }

    /**
     * Sets the DNS mail recipients.
     *
     * @param string $dnsMailRecipients
     *
     * @return $this
     */
    public function setDnsMailRecipients($dnsMailRecipients)
    {
        $this->dnsMailRecipients = $dnsMailRecipients;

        return $this;
    }

    /**
     * Gets the DNS mail template.
     *
     * @return string
     */
    public function getDnsMailTemplate()
    {
        return $this->dnsMailTemplate;
    }

    /**
     * Sets the DNS mail template.
     *
     * @param string $dnsMailTemplate
     *
     * @return $this
     */
    public function setDnsMailTemplate($dnsMailTemplate)
    {
        $this->dnsMailTemplate = $dnsMailTemplate;

        return $this;
    }

    /**
     * Gets the sock domain.
     *
     * @return string
     */
    public function getSockDomain()
    {
        return $this->sockDomain;
    }

    /**
     * Sets the sock domain.
     *
     * @param string $sockDomain
     *
     * @return $this
     */
    public function setSockDomain($sockDomain)
    {
        $this->sockDomain = $sockDomain;

        return $this;
    }

    /**
     * Gets the sock user token.
     *
     * @return string
     */
    public function getSockUserToken()
    {
        return $this->sockUserToken;
    }

    /**
     * Sets the sock user token.
     *
     * @param string $sockUserToken
     *
     * @return $this
     */
    public function setSockUserToken($sockUserToken)
    {
        $this->sockUserToken = $sockUserToken;

        return $this;
    }

    /**
     * Gets the sock client token.
     *
     * @return string
     */
    public function getSockClientToken()
    {
        return $this->sockClientToken;
    }

    /**
     * Sets the sock client token.
     *
     * @param string $sockClientToken
     *
     * @return $this
     */
    public function setSockClientToken($sockClientToken)
    {
        $this->sockClientToken = $sockClientToken;

        return $this;
    }

    /**
     * Gets the app environment settings for an app environment.
     *
     * @param null|string|AppEnvironment $appEnvironment
     *
     * @return AppEnvironmentSettings|AppEnvironmentSettings[]
     */
    public function getAppEnvironmentSettings($appEnvironment = null)
    {
        if (null === $appEnvironment) {
            return $this->appEnvironmentSettings;
        }

        if ($appEnvironment instanceof AppEnvironment) {
            $appEnvironment = $appEnvironment->getNameCanonical();
        }

        foreach ($this->appEnvironmentSettings as $settings) {
            if ($settings->getEnvironment() === $appEnvironment) {
                return $settings;
            }
        }

        throw new \InvalidArgumentException(sprintf('invalid appenvironment given: %s', $appEnvironment));
    }

    /**
     * Sets the app environment settings.
     *
     * @param AppEnvironmentSettings[] $appEnvironmentSettings
     *
     * @return $this
     */
    public function setAppEnvironmentSettings($appEnvironmentSettings)
    {
        $this->appEnvironmentSettings = $appEnvironmentSettings;

        return $this;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @var SshKeyGroup[]|array|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup", inversedBy="defaultSettings")
     */
    protected $defaultSshKeyGroups;

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

    public function __construct()
    {
        $this->defaultSshKeyGroups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
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
     * @return string
     */
    public function getDefaultSockSshKeys()
    {
        return $this->defaultSockSshKeys;
    }

    /**
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
     * @return mixed
     */
    public function getDefaultSshKeyGroups()
    {
        return $this->defaultSshKeyGroups;
    }

    /**
     * @param mixed $defaultSshKeyGroups
     *
     * @return $this
     */
    public function setDefaultSshKeyGroups($defaultSshKeyGroups)
    {
        $this->defaultSshKeyGroups = $defaultSshKeyGroups;

        return $this;
    }

    /**
     * @return string
     */
    public function getDnsMailRecipients()
    {
        return $this->dnsMailRecipients;
    }

    /**
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
     * @return string
     */
    public function getDnsMailTemplate()
    {
        return $this->dnsMailTemplate;
    }

    /**
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
     * @return string
     */
    public function getSockDomain()
    {
        return $this->sockDomain;
    }

    /**
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
     * @return string
     */
    public function getSockUserToken()
    {
        return $this->sockUserToken;
    }

    /**
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
     * @return string
     */
    public function getSockClientToken()
    {
        return $this->sockClientToken;
    }

    /**
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
     * @param null|string|AppEnvironment $appEnvironment
     *
     * @return AppEnvironmentSettings|AppEnvironmentSettings[]
     */
    public function getAppEnvironmentSettings($appEnvironment = null)
    {
        if ($appEnvironment === null) {
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
     * @param AppEnvironmentSettings[] $appEnvironmentSettings
     *
     * @return $this
     */
    public function setAppEnvironmentSettings($appEnvironmentSettings)
    {
        $this->appEnvironmentSettings = $appEnvironmentSettings;

        return $this;
    }

    /**
     * Add defaultSshKeyGroups.
     *
     * @param \DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup $defaultSshKeyGroups
     *
     * @return Settings
     */
    public function addDefaultSshKeyGroup(SshKeyGroup $defaultSshKeyGroups)
    {
        $this->defaultSshKeyGroups[] = $defaultSshKeyGroups;

        return $this;
    }

    /**
     * Remove defaultSshKeyGroups.
     *
     * @param \DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup $defaultSshKeyGroups
     */
    public function removeDefaultSshKeyGroup(SshKeyGroup $defaultSshKeyGroups)
    {
        $this->defaultSshKeyGroups->removeElement($defaultSshKeyGroups);
    }
}

<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApplicationServer
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 *
 * @ORM\Entity()
 */
class ApplicationServer
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var AbstractApplication
     *
     * @ORM\ManyToOne(targetEntity="AbstractApplication", inversedBy="applicationServers")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @var Server
     *
     * @ORM\ManyToOne(targetEntity="Server",inversedBy="applicationServers")
     * @ORM\JoinColumn(name="server_id",referencedColumnName="id")
     */
    protected $server;

    /**
     * @param AbstractApplication $application
     */
    public function setApplication(AbstractApplication $application)
    {
        $this->application = $application;
    }

    /**
     * @return AbstractApplication
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Server $server
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->getServer()->getName();
    }

    /**
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->getServer()->getEnvironment()->getName();
    }
}
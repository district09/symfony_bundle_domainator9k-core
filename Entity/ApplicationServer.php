<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;


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

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @var integer
     *
     * @ORM\Column(name="ssh_port",type="integer",nullable=true)
     * @Assert\NotBlank()
     */
    protected $sshPort;

    /**
     * @var string
     *
     * @ORM\Column(name="ssh_user",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $sshUser;

    /**
     * @var string
     *
     * @ORM\Column(name="ssh_password",type="string",nullable=true)
     * @Assert\NotBlank()
     */
    protected $sshPassword;

    /**
     * @param AbstractApplication $application
     */
    public function setApplication(AbstractApplication $application){
        $this->application = $application;
    }

    /**
     * @return AbstractApplication
     */
    public function getApplication(){
        return $this->application;
    }

    /**
     * @param Server $server
     */
    public function setServer(Server $server){
        $this->server = $server;
    }

    /**
     * @return Server
     */
    public function getServer(){
        return $this->server;
    }

    /**
     * @return int
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }

    /**
     * @param int $sshPort
     */
    public function setSshPort(int $sshPort)
    {
        $this->sshPort = $sshPort;
    }

    /**
     * @return string
     */
    public function getSshUser()
    {
        return $this->sshUser;
    }

    /**
     * @param string $sshUser
     */
    public function setSshUser(string $sshUser)
    {
        $this->sshUser = $sshUser;
    }

    /**
     * @return string
     */
    public function getSshPassword()
    {
        return $this->sshPassword;
    }

    /**
     * @param string $sshPassword
     */
    public function setSshPassword(string $sshPassword)
    {
        $this->sshPassword = $sshPassword;
    }

    /**
     * @return string
     */
    public function getServerName(){
        return $this->getServer()->getName();
    }
}
<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="server_settings")
 */
class ServerSettings
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AppEnvironment
     * @ORM\OneToOne(targetEntity="AppEnvironment", cascade={"all"}, inversedBy="serverSettings")
     * * @ORM\JoinColumn(name="environment_id", referencedColumnName="id", nullable=true)
     */
    protected $appEnvironment;

    /**
     * @var int
     * @ORM\Column(name="port_ssh", type="integer", nullable=false, options={"default": 22})
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="5")
     */
    protected $portSsh = 22;

    /**
     * @var string
     * @ORM\Column(name="user", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     */
    protected $user = 'root';

    /**
     * @var string
     * @ORM\Column(name="password", type="string", nullable=false)
     * @Assert\Length(min="1", max="255")
     */
    protected $password;

    /**
     * @var int
     * @ORM\Column(name="sock_server_account", type="integer", nullable=true)
     */
    protected $sockAccountId;

    /**
     * Creates new server settings.
     *
     * @param AppEnvironment $appEnvironment
     * @param string $user
     * @param string $password
     */
    public function __construct(AppEnvironment $appEnvironment, $user, $password)
    {
        $this->appEnvironment = $appEnvironment;
        $this->user = $user;
        $this->password = $password;
    }

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
     * Gets the app environment.
     *
     * @return AppEnvironment
     */
    public function getAppEnvironment()
    {
        return $this->appEnvironment;
    }

    /**
     * Sets the app environment.
     *
     * @param AppEnvironment $appEnvironment
     *
     * @return $this
     */
    public function setAppEnvironment($appEnvironment)
    {
        $this->appEnvironment = $appEnvironment;

        return $this;
    }

    /**
     * Gets the ssh port.
     *
     * @return int
     */
    public function getPortSsh()
    {
        return $this->portSsh;
    }

    /**
     * Sets the ssh port.
     *
     * @param int $portSsh
     *
     * @return $this
     */
    public function setPortSsh($portSsh)
    {
        $this->portSsh = $portSsh;

        return $this;
    }

    /**
     * Gets the user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the user.
     *
     * @param string $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Gets the sock account id.
     *
     * @return mixed
     */
    public function getSockAccountId()
    {
        return $this->sockAccountId;
    }

    /**
     * Sets the sock account id.
     * 
     * @param mixed $sockAccountId
     *
     * @return $this
     */
    public function setSockAccountId($sockAccountId)
    {
        $this->sockAccountId = $sockAccountId;

        return $this;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'virtual_server')]
#[ORM\Entity]
class VirtualServer
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'host', type: 'string', length: 20, nullable: false)]
    #[Assert\NotBlank]
    protected $host;

    /**
     * @var integer
     */
    #[ORM\Column(name: 'port', type: 'integer', nullable: false)]
    #[Assert\NotBlank]
    protected $port;

    /**
     * @var Environment
     */
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Environment::class, inversedBy: 'virtualServers')]
    #[Assert\NotNull]
    protected $environment;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'task_server', type: 'boolean')]
    protected $taskServer;

    public function __construct()
    {
        $this->port = 22;
    }

    /**
     * @return string
     */
    public static function getSettingImplementationName()
    {
        return 'server';
    }

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name.
     *
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
     * @return bool
     */
    public function isTaskServer()
    {
        return $this->taskServer;
    }

    /**
     * @param bool $taskServer
     */
    public function setTaskServer($taskServer)
    {
        $this->taskServer = $taskServer;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }
}

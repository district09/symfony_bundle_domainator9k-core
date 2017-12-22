<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DateTime;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DigipolisGent\Domainator9k\CoreBundle\Entity\Repository\BuildRepository")
 * @ORM\Table(name="build")
 */
class Build
{

    const STATUS_NEW = 'new';
    const STATUS_PROCESSED = 'processed';
    const IN_PROGRESS = 'in_progress';

    use IdentifiableTrait;

    /**
     * @var DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @var string
     * @ORM\Column(name="log", type="text", nullable=true)
     */
    protected $log;

    /**
     * @var int
     * @ORM\Column(name="pid", type="integer", nullable=true, options={"default"=NULL})
     */
    protected $pid;

    /**
     * @var string
     *
     * @ORM\Column(name="status",type="string")
     */
    protected $status;

    /**
     * @var ApplicationEnvironment
     *
     * @ORM\ManyToOne(targetEntity="ApplicationEnvironment",inversedBy="builds")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $applicationEnvironment;

    /**
     * Build constructor.
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->status = self::STATUS_NEW;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * Gets the log.
     *
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Sets the log.
     *
     * @param string $log
     *
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Gets the process id.
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Sets the process id.
     *
     * @param int $pid
     *
     * @return $this
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function setApplicationEnvironment(ApplicationEnvironment $applicationEnvironment)
    {
        $this->applicationEnvironment = $applicationEnvironment;
    }

    /**
     * @return ApplicationEnvironment
     */
    public function getApplicationEnvironment()
    {
        return $this->applicationEnvironment;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}

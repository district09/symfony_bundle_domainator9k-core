<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DateTime;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DigipolisGent\Domainator9k\CoreBundle\Entity\Repository\TaskRepository")
 * @ORM\Table(name="task")
 */
class Task
{

    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCEL= 'cancel';

    const TYPE_BUILD = 'build';
    const TYPE_DESTROY = 'destroy';

    use IdentifiableTrait;

    /**
     * @var ApplicationEnvironment
     *
     * @ORM\ManyToOne(targetEntity="ApplicationEnvironment",inversedBy="tasks")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $applicationEnvironment;

    /**
     * @var string
     *
     * @ORM\Column(name="type",type="string")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="status",type="string")
     */
    protected $status;

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
     * Build constructor.
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->status = self::STATUS_NEW;
    }

    /**
     * @return ApplicationEnvironment
     */
    public function getApplicationEnvironment()
    {
        return $this->applicationEnvironment;
    }

    /**
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function setApplicationEnvironment(ApplicationEnvironment $applicationEnvironment)
    {
        $this->applicationEnvironment = $applicationEnvironment;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
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

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
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
     * Mark this task as in progress.
     */
    public function setInProgress()
    {
        $this->setStatus(static::STATUS_IN_PROGRESS);
    }

    /**
     * Mark this task as processed.
     */
    public function setProcessed()
    {
        $this->setStatus(static::STATUS_PROCESSED);
    }

    /**
     * Mark this task as failed.
     */
    public function setFailed()
    {
        $this->setStatus(static::STATUS_FAILED);
    }

    /**
     * Mark this task as in cancelled.
     */
    public function setCancelled()
    {
        $this->setStatus(static::STATUS_CANCEL);
    }

    /**
     * Check if this task is new.
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->getStatus === static::STATUS_NEW;
    }

    /**
     * Check if this task is in progress.
     *
     * @return boolean
     */
    public function isInProgress()
    {
        $this->setStatus(static::STATUS_IN_PROGRESS);
    }

    /**
     * Check if this task is processed.
     *
     * @return boolean
     */
    public function isProcessed()
    {
        $this->setStatus(static::STATUS_PROCESSED);
    }

    /**
     * Check if this task is failed.
     *
     * @return boolean
     */
    public function isFailed()
    {
        $this->setStatus(static::STATUS_FAILED);
    }

    /**
     * Check if this task is cancelled.
     *
     * @return boolean
     */
    public function isCancelled()
    {
        $this->setStatus(static::STATUS_CANCEL);
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Service\ProvisionService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TaskService
 *
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TaskService
{

    const LOG_TYPE_INFO = 'info';
    const LOG_TYPE_WARNING = 'warning';
    const LOG_TYPE_ERROR = 'error';
    const LOG_TYPE_SUCCESS = 'success';
    const LOG_TYPE_FAILED = 'failed';

    /**
     * The entity manager service.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * The provision service.
     *
     * @var ProvisionService
     */
    private $provisionService;

    /**
     * Class constructor.
     *
     * @param EntityManagerInterface $entityManager
     *   The entity manager service.
     * @param ProvisionService $provisionService
     *   The provision service.
     */
    public function __construct(EntityManagerInterface $entityManager, ProvisionService $provisionService)
    {
        $this->entityManager = $entityManager;
        $this->provisionService = $provisionService;
    }

    /**
     * Run a task.
     *
     * @param Task $task
     *   The task to run.
     */
    public function run(Task $task)
    {
        if ($task->getStatus() !== Task::STATUS_NEW) {
            throw new \InvalidArgumentException(sprintf('Task "%s" cannot be restarted.', $task->getId()));
        }

        // Set the task in progress.
        $task->setStatus(Task::STATUS_IN_PROGRESS);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->provisionService->run($task);

        // Update the status.
        if ($task->getStatus() === Task::STATUS_IN_PROGRESS) {
            $task->setStatus(Task::STATUS_PROCESSED);
        }

        // Add a log message or simply persist any changes.
        switch ($task->getStatus()) {
            case Task::STATUS_PROCESSED:
                $this->addLogMessage($task, '', '', 0);
                $this->addSuccessLogMessage($task, 'Task run completed.', 0);
                break;

            case Task::STATUS_FAILED:
                $this->addLogMessage($task, '', '', 0);
                $this->addFailedLogMessage($task, 'Task run failed.', 0);
                break;

            default:
                $this->entityManager->persist($task);
                $this->entityManager->flush();
                break;
        }
    }

    /**
     * Run the next task of the specified type.
     *
     * @param string $type
     *   The task type to run.
     */
    public function runNext(string $type)
    {
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->getNextTask($type);

        if ($task) {
            $this->run($task);
        }
    }

    /**
     * Cancel a task.
     *
     * @param Task $task
     *   The task to cancel.
     */
    public function cancel(Task $task)
    {
        if ($task->getStatus() !== Task::STATUS_NEW) {
            throw new \InvalidArgumentException(sprintf('Task %s cannot be cancelled.', $task->getId()));
        }

        $task->setStatus(Task::STATUS_CANCEL);
        $this->addInfoLogMessage($task, 'Task run cancelled.');
    }

    /**
     * Add a log header.
     *
     * @param Task $task
     *   The task object.
     * @param string $header
     *   The log header.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addLogHeader(Task $task, string $header, int $indent = 0, bool $persist = false): self
    {
        if ($log = $task->getLog()) {
            $log .= PHP_EOL;
        }

        $header = trim($header);
        $header = preg_replace('/[\r\n]+/', ' ', $header);
        $header = '### ' . $header . ' ###';

        $log .= $this->indentText($header, $indent);

        $task->setLog($log);

        if ($persist) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * Add a log message.
     *
     * @param Task $task
     *   The task object.
     * @param string $type
     *   The log type.
     * @param string $message
     *   The log message.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addLogMessage(Task $task, string $type, string $message, int $indent = 1, bool $persist = true): self
    {
        if ($log = $task->getLog()) {
            $log .= PHP_EOL;
        }

        $message = trim($message);
        $message = str_replace(["\r\n", "\r", "\n"], PHP_EOL, $message);

        if ($type && $type !== self::LOG_TYPE_INFO) {
            $message .= ' [' . $type . ']';
        }

        $log .= $this->indentText($message, $indent);

        $task->setLog($log);

        if ($persist) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * Add an "info" log message.
     *
     * @param Task $task
     *   The task object.
     * @param string $message
     *   The log message.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addInfoLogMessage(Task $task, string $message, int $indent = 1, bool $persist = true): self
    {
        return $this->addLogMessage($task, self::LOG_TYPE_INFO, $message, $indent, $persist);
    }

    /**
     * Add a "warning" log message.
     *
     * @param Task $task
     *   The task object.
     * @param string $message
     *   The log message.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addWarningLogMessage(Task $task, string $message, int $indent = 1, bool $persist = false): self
    {
        return $this->addLogMessage($task, self::LOG_TYPE_WARNING, $message, $indent, $persist);
    }

    /**
     * Add an "error" log message.
     *
     * @param Task $task
     *   The task object.
     * @param string $message
     *   The log message.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addErrorLogMessage(Task $task, string $message, int $indent = 1, bool $persist = false): self
    {
        return $this->addLogMessage($task, self::LOG_TYPE_ERROR, $message, $indent, $persist);
    }

    /**
     * Add a "success" log message.
     *
     * @param Task $task
     *   The task object.
     * @param string $message
     *   The log message.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addSuccessLogMessage(Task $task, string $message, int $indent = 1, bool $persist = true): self
    {
        return $this->addLogMessage($task, self::LOG_TYPE_SUCCESS, $message, $indent, $persist);
    }

    /**
     * Add a "failed" log message.
     *
     * @param Task $task
     *   The task object.
     * @param string $message
     *   The log message.
     * @param int $indent
     *   Number of levels to indent.
     * @param bool $persist
     *   Persist the task to the database.
     *
     * @return self
     */
    public function addFailedLogMessage(Task $task, string $message, int $indent = 1, bool $persist = true): self
    {
        return $this->addLogMessage($task, self::LOG_TYPE_FAILED, $message, $indent, $persist);
    }

    /**
     * Indent a text.
     *
     * @param string $text
     *   The text to indent.
     * @param int $indent
     *   Number of levels to indent.
     *
     * @return string
     *   The indented text.
     */
    protected function indentText(string $text, int $indent)
    {
        return preg_replace_callback('/(^|[\r\n]+)(\t+)?/', function($matches) use ($indent) {
            $suffix = '';

            if ($indent) {
                $suffix .= str_repeat("\t", $indent);
            }

            if (isset($matches[2])) {
                $suffix .= str_repeat('    ', strlen($matches[2]));
            }

            return $matches[1] . $suffix;
        }, $text);
    }
}

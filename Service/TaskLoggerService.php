<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TaskLoggerService
 *
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TaskLoggerService
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
    protected $entityManager;

    /**
     * Class constructor.
     *
     * @param EntityManagerInterface $entityManager
     *   The entity manager service.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

    /**
     * Generate an HTML safe task log.
     *
     * @param string $log
     *   The task log.
     *
     * @return string
     *   The escaped log.
     */
    public function escapeLog(string $log): string
    {
        // Default HTML escaping.
        $log = htmlspecialchars($log, ENT_QUOTES, 'UTF-8', false);
        $log = str_replace(["\r\n", "\r"], "\n", $log);

        // Make titles bold.
        $log = preg_replace('/^(\t*)### (.+) ###$/m', '$1<strong>$2</strong>', $log);

        // Count the number of lines.
        $lineCount = substr_count($log, "\n") + 1;

        // Get the line number width.
        $lineNumberWidth = \strlen($lineCount);

        // Wrap all lines
        $lineNumber = 0;
        $prevIndents = [];
        $log = (string) preg_replace_callback(
            '/^(\t*)(?:(.+?)(?: \[(warning|error|success|failed)\])?)?$/m',
            function ($matches) use (&$lineNumber, &$prevIndents, $lineCount, $lineNumberWidth) {
                if (isset($matches[2])) {
                    $indent = \strlen($matches[1]);
                    $line = $matches[2];
                } else {
                    $indent = $prevIndents[0] ?? 0;
                    $line = '';
                }

                $status = $matches[3] ?? null;

                // Apply the message wrapper with indentation.
                $line = sprintf(
                    '<div class="%s" style="padding-left: %sem;">%s</div>',
                    'message message--indent-' . $indent,
                    $indent * 1.5,
                    $line
                );

                // Add the line number.
                $lineNumber++;
                $line = sprintf(
                    '<div class="%s">%' . $lineNumberWidth . 's</div>%s',
                    'number number--' . $lineNumber,
                    $lineNumber,
                    $line
                );

                // Add the line status.
                if ($status !== null) {
                    $line = sprintf(
                        '%s<div class="%s">[%s]</div>',
                        $line,
                        'status status--' . $status,
                        $status
                    );
                }

                // Wrap the whole line.
                $class = 'line line--' . $lineNumber;

                if ($lineNumber === 1) {
                    $class .= ' line--first';
                } elseif ($lineNumber === $lineCount) {
                    $class .= ' line--last';
                }

                if ($status !== null) {
                    $class .= ' line--status-' . $status;
                }

                $line = sprintf(
                    '<div class="%s">%s</div>',
                    $class,
                    $line
                );

                if (!$prevIndents || $indent > $prevIndents[0]) {
                    // Start a new indentation group.
                    $line = sprintf(
                        '<div class="%s">%s',
                        'group group--indent-' . $indent . ' group--number-' . $lineNumber,
                        $line
                    );
                    array_unshift($prevIndents, $indent);
                } elseif ($indent < $prevIndents[0]) {
                    // Close the previous groups.
                    do {
                        $line = '</div>' . $line;
                        array_shift($prevIndents);
                    } while ($indent < $prevIndents[0]);
                }

                return $line;
            },
            $log
        );

        if ($prevIndents) {
            $log .= str_repeat('</div>', \count($prevIndents));
        }

        return $log;
    }
}

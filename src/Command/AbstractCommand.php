<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Command;

use DigipolisGent\Domainator9k\CoreBundle\Service\TaskRunnerService;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{

    /**
     * @var TaskRunnerService
     */
    protected $taskRunner;

    public function __construct(TaskRunnerService $taskRunner)
    {
        parent::__construct();
        $this->taskRunner = $taskRunner;
    }

    /**
     * Run the next task of the specified type.
     *
     * @param string $type
     *   The task type.
     */
    protected function runNextTask(string $type)
    {
        $this->taskRunner->runNext($type);
    }
}

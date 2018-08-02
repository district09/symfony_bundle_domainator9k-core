<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Command;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand
 *
 * @package DigipolisGent\Domainator9k\CoreBundle\Command
 */
class BuildCommand extends AbstractCommand
{

    /**
     * Configure the command properties.
     */
    public function configure()
    {
        $this->setName('domainator:build');
    }

    /**
     * Run the command.
     *
     * @param InputInterface $input
     *   The input.
     * @param OutputInterface $output
     *   The output.
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runNextTask(Task::TYPE_BUILD);
    }
}

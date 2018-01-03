<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Command;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Event\DestroyEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DestroyCommand
 * @package DigipolisGent\Domainator9k\CoreBundle\Command
 */
class DestroyCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('domainator:destroy');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $task = $entityManager->getRepository(Task::class)->getNextTask(Task::TYPE_DESTROY);

        if (!$task) {
            return;
        }

        $event = new DestroyEvent($task);
        $eventDispatcher->dispatch(DestroyEvent::NAME, $event);
    }
}
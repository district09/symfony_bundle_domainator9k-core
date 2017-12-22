<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Command;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand
 * @package DigipolisGent\Domainator9k\CoreBundle\Command
 */
class BuildCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('domainator:build');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $build = $entityManager->getRepository(Build::class)->getNextBuild();

        if (!$build) {
            return;
        }

        $event = new BuildEvent($build);
        $eventDispatcher->dispatch(BuildEvent::NAME, $event);
    }
}
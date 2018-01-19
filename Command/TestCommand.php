<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Command;


use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand
 * @package DigipolisGent\Domainator9k\CoreBundle\Command
 */
class TestCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('domainator:test');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ObjectManager $manager */
        $manager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $metadatum = $manager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatum as $metadata){

        }
    }
}
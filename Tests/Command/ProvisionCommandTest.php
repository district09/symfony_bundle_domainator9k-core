<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Command;

use DigipolisGent\Domainator9k\CoreBundle\Command\ProvisionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Description of ProvisionCommandTest
 *
 * @author Jelle Sebreghts
 */
class ProvisionCommandTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
    }

    public function testProvisionApplication()
    {
        $application = new Application();
        $application->add(new ProvisionCommand());

        $command = $application->find('digip:provision');
        $command->setContainer($this->container);

        $tester = new CommandTester($command);
        /*$tester->execute(
            array_merge(array('command' => $command->getName()), $input)
        );

        $this->assertEquals('Keyspace ' . $input['keyspace'] . ' successfully created at #mockServer#' . PHP_EOL, $tester->getDisplay());*/
    }

}

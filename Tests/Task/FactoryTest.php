<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Task\Factory;
use DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem\CreateFileTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\Provision\FilesystemTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use DigipolisGent\SockAPIBundle\JsonModel\Server;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of FactoryTest
 *
 * @author Jelle Sebreghts
 */
class FactoryTest extends TestCase
{

    use DataGenerator;

    protected $shellFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->shellFactory = $this->getMockBuilder(SshShellFactoryInterface::class)->getMock();
    }

    public function testSetDefaultOptions() {
        $factory = $this->getFactory();
        $options = [$this->getAlphaNumeric()];
        $factory->setDefaultOptions($options);
        $this->assertEquals($options, $factory->getDefaultOptions());
    }

    public function testAddTaskDefinition() {
        $factory = $this->getFactory();
        $factory->addTaskDefinition(CreateFileTask::class);
        $factory->addTaskDefinition(FilesystemTask::class);
        $createFileOptions = [
            'path' => $this->getAlphaNumeric(),
            'content' => $this->getAlphaNumeric(),
            'host' => $this->getAlphaNumeric(),
            'password' => $this->getAlphaNumeric(),
            'user' => $this->getAlphaNumeric(),
            'authtype' => SshShellFactory::AUTH_TYPE_CREDENTIALS,
            'appEnvironment' => $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock(),
        ];
        $filesystemOptions = [
            'settings' => $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock(),
            'applicationTypeBuilder' => $this->getMockBuilder(ApplicationTypeBuilder::class)->disableOriginalConstructor()->getMock(),
            'appEnvironment' => $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock(),
            'servers' => [
                $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock(),
            ],
        ];
        $this->assertInstanceOf(CreateFileTask::class, $factory->create(CreateFileTask::getName(), $createFileOptions));
        $this->assertInstanceOf(FilesystemTask::class, $factory->create(FilesystemTask::getName(), $filesystemOptions));
    }

    /**
     * @expectedException \InvalidArgumentexception
     */
    public function testAddInvalidTaskDefinition() {
        $factory = $this->getFactory();
        $factory->addTaskDefinition(stdClass::class);
    }

    /**
     * @expectedException \InvalidArgumentexception
     */
    public function testInvalidCreate() {
        $factory = $this->getFactory();
        $factory->create($this->getAlphaNumeric());
    }

    public function testCreateRunner() {
        $factory = $this->getFactory();
        $this->assertInstanceOf(TaskRunner::class, $factory->createRunner());
    }

    protected function getFactory() {
        return new Factory($this->shellFactory);
    }
}

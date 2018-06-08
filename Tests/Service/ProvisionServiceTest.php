<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\ProvisionerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\ProvisionService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TokenService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Foo;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Qux;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ProvisionServiceTest extends TestCase
{

    public function testBuild()
    {
        $task = new Task();
        $task->setType(Task::TYPE_BUILD);

        $buildProvisioners = [];
        foreach (range(0, 5) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($task)
                ->willReturn(null);
            $buildProvisioners[] = $mock;
        }
        $destroyProvisioners = [];
        foreach (range(0, 5) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->never())
                ->method('run');
            $destroyProvisioners[] = $mock;
        }
        $provisionService = new ProvisionService($buildProvisioners, $destroyProvisioners);
        $provisionService->run($task);
    }

    public function testDestroy()
    {
        $task = new Task();
        $task->setType(Task::TYPE_DESTROY);

        $buildProvisioners = [];
        foreach (range(0, 5) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->never())
                ->method('run');
            $buildProvisioners[] = $mock;
        }

        $destroyProvisioners = [];
        foreach (range(0, 5) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($task)
                ->willReturn(null);
            $destroyProvisioners[] = $mock;
        }
        $provisionService = new ProvisionService($buildProvisioners, $destroyProvisioners);
        $provisionService->run($task);
    }

    public function testFailedBuild()
    {
        $task = new Task();
        $task->setType(Task::TYPE_BUILD);

        $buildProvisioners = [];
        foreach (range(0, 3) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($task)
                ->willReturn(null);
            $buildProvisioners[] = $mock;
        }
        $mock = $this->getMockBuilder(ProvisionerInterface::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('run')
            ->with($task)
            ->willReturnCallback(function (Task $task) {
                $task->setFailed();
            });
        $buildProvisioners[] = $mock;

        foreach (range(0, 2) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->never())
                ->method('run');
            $buildProvisioners[] = $mock;
        }
        $provisionService = new ProvisionService($buildProvisioners, []);
        $provisionService->run($task);
    }

    public function testFailedDestroy()
    {
        $task = new Task();
        $task->setType(Task::TYPE_DESTROY);

        $destroyProvisioners = [];
        foreach (range(0, 3) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->once())
                ->method('run')
                ->with($task)
                ->willReturn(null);
            $destroyProvisioners[] = $mock;
        }
        $mock = $this->getMockBuilder(ProvisionerInterface::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('run')
            ->with($task)
            ->willReturnCallback(function (Task $task) {
                $task->setFailed();
            });
        $destroyProvisioners[] = $mock;

        foreach (range(0, 2) as $i) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)
                ->getMock();
            $mock->expects($this->never())
                ->method('run');
            $destroyProvisioners[] = $mock;
        }
        $provisionService = new ProvisionService([], $destroyProvisioners);
        $provisionService->run($task);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task type (.*) is not supported\./
     */
    public function testInvalidType()
    {
        $task = new Task();
        $task->setType(uniqid());
        $provisionService = new ProvisionService([], []);
        $provisionService->run($task);
    }
}

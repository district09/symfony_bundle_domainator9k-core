<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\CacheClearer\CacheClearerInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\CacheClearBuildProvisioner;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use PHPUnit\Framework\TestCase;

class CacheClearBuildProvisionerTest extends TestCase
{
    public function testGetName()
    {
        $cliFactoryProvider = new CliFactoryProvider();
        $cacheClearProvider = new CacheClearProvider();
        $taskLoggerService = $this->getMockBuilder(TaskLoggerService::class)->disableOriginalConstructor()->getMock();
        $provisioner = new CacheClearBuildProvisioner($cliFactoryProvider, $cacheClearProvider, $taskLoggerService);
        $this->assertEquals('Clear caches', $provisioner->getName());
    }

    public function testRun()
    {
        $cliFactoryProvider = new CliFactoryProvider();
        $cacheClearProvider = new CacheClearProvider();

        $application = $this->getMockBuilder(AbstractApplication::class)->getMock();
        $environment = $this->getMockBuilder(Environment::class)->getMock();

        $appEnv = $this->getMockBuilder(ApplicationEnvironment::class)->getMock();
        $appEnv->expects($this->once())->method('getApplication')->willReturn($application);
        $appEnv->expects($this->once())->method('getEnvironment')->willReturn($environment);

        $task = $this->getMockBuilder(Task::class)->getMock();
        $task->expects($this->once())->method('getApplicationEnvironment')->willReturn($appEnv);

        $cli = $this->getMockBuilder(CliInterface::class)->getMock();

        $cliFactory = $this->getMockBuilder(CliFactoryInterface::class)->getMock();
        $cliFactory->expects($this->once())->method('create')->with($appEnv)->willReturn($cli);

        $cliFactoryProvider->registerCliFactory($cliFactory, get_class($appEnv));

        $clearer = $this->getMockBuilder(CacheClearerInterface::class)->getMock();
        $clearer->expects($this->once())->method('clearCache')->with($appEnv, $cli)->willReturn(true);

        $cacheClearProvider->registerCacheClearer($clearer, get_class($application));

        $taskLoggerService = $this->getMockBuilder(TaskLoggerService::class)->disableOriginalConstructor()->getMock();

        $provisioner = new CacheClearBuildProvisioner($cliFactoryProvider, $cacheClearProvider, $taskLoggerService);
        $provisioner->setTask($task);
        $provisioner->run();
    }
}

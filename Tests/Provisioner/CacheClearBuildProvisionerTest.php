<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\CacheClearer\CacheClearerInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\CacheClearBuildProvisioner;
use PHPUnit\Framework\TestCase;

class CacheClearBuildProvisionerTest extends TestCase
{
    public function testGetName()
    {
        $cliFactoryProvider = new CliFactoryProvider();
        $cacheClearProvider = new CacheClearProvider();
        $provisioner = new CacheClearBuildProvisioner($cliFactoryProvider, $cacheClearProvider);
        $this->assertEquals('Clear caches', $provisioner->getName());
    }

    public function testRun()
    {
        $cliFactoryProvider = new CliFactoryProvider();
        $cacheClearProvider = new CacheClearProvider();

        $application = $this->getMockBuilder(AbstractApplication::class)->getMock();

        $appEnv = $this->getMockBuilder(ApplicationEnvironment::class)->getMock();
        $appEnv->expects($this->once())->method('getApplication')->willReturn($application);

        $task = $this->getMockBuilder(Task::class)->getMock();
        $task->expects($this->once())->method('getApplicationEnvironment')->willReturn($appEnv);

        $cli = $this->getMockBuilder(CliInterface::class)->getMock();

        $cliFactory = $this->getMockBuilder(CliFactoryInterface::class)->getMock();
        $cliFactory->expects($this->once())->method('create')->with($appEnv)->willReturn($cli);

        $cliFactoryProvider->registerCliFactory($cliFactory, get_class($appEnv));

        $clearer = $this->getMockBuilder(CacheClearerInterface::class)->getMock();
        $clearer->expects($this->once())->method('clearCache')->with($appEnv, $cli);

        $cacheClearProvider->registerCacheClearer($clearer, get_class($application));

        $provisioner = new CacheClearBuildProvisioner($cliFactoryProvider, $cacheClearProvider);
        $provisioner->setTask($task);
        $provisioner->run();
    }
}

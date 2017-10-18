<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DateTime;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of AppEnvironmentTest
 *
 * @author Jelle Sebreghts
 */
class AppEnvironmentTest extends TestCase
{

    public function testContstructor()
    {
        $name = str_repeat(uniqid(mt_rand(), true), mt_rand(1, 5));
        $devPermissions = (bool) (microtime() % 2);
        $prod = (bool) (microtime() % 3);
        $mockApp = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $mockApp->expects($this->once())->method('getCron')->willReturn('cron_' . $name);
        $mockApp->expects($this->once())->method('hasDatabase')->willReturn(true);
        $mockApp->expects($this->any())->method('getNameCanonical')->willReturn($name);
        $mockApp->expects($this->any())->method('getName')->willReturn($name);

        $appEnv = new AppEnvironment($mockApp, $name, $devPermissions, $prod);
        $this->assertEquals($appEnv->getName(), $name);
        $this->assertEquals($appEnv->getApplication(), $mockApp);
        $this->assertInstanceOf(DateTime::class, $appEnv->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $appEnv->getUpdatedAt());
        $this->assertNotEmpty($appEnv->getSalt());
        $this->assertInstanceOf(ArrayCollection::class, $appEnv->getUsers());
        $this->assertInstanceOf(ArrayCollection::class, $appEnv->getRoles());
        $this->assertEquals('cron_' . $name, $appEnv->getCron());
        $this->assertEquals('', $appEnv->getGitRef());
        $this->assertEquals($devPermissions, $appEnv->isDevPermissions());
        $this->assertEquals($prod, $appEnv->isProd());

        // Assert server settings are set.
        $this->assertInstanceOf(ServerSettings::class, $appEnv->getServerSettings());
        $this->assertLessThanOrEqual(14, strlen($appEnv->getServerSettings()->getUser()));
        $this->assertEquals(substr($mockApp->getNameCanonical(), 0, 14), $appEnv->getServerSettings()->getUser());
        $this->assertNotEmpty($appEnv->getServerSettings()->getPassword());
        // Assert database settings are set.
        $this->assertInstanceOf(DatabaseSettings::class, $appEnv->getDatabaseSettings());
        $this->assertEquals($appEnv, $appEnv->getDatabaseSettings()->getAppEnvironment());
        $expectedDbName = $appEnv->getServerSettings()->getUser() . '_' . substr($mockApp->getNameCanonical(), 0, 1);
        $this->assertSame($expectedDbName, $appEnv->getDatabaseSettings()->getName());
        $this->assertNotEmpty($appEnv->getDatabaseSettings()->getPassword());
    }

}

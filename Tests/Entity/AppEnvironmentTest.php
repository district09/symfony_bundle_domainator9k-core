<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DateTime;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use ReflectionObject;
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
        $mockApp = $this->getMockApp($name);

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
        $this->assertSame($appEnv, $appEnv->getServerSettings()->getAppEnvironment());
        // Assert database settings are set.
        $this->assertInstanceOf(DatabaseSettings::class, $appEnv->getDatabaseSettings());
        $this->assertEquals($appEnv, $appEnv->getDatabaseSettings()->getAppEnvironment());
        $expectedDbName = $appEnv->getServerSettings()->getUser() . '_' . substr($mockApp->getNameCanonical(), 0, 1);
        $this->assertSame($expectedDbName, $appEnv->getDatabaseSettings()->getName());
        $this->assertNotEmpty($appEnv->getDatabaseSettings()->getPassword());
    }

    public function testContstructorWithParent()
    {
        $name = str_repeat(uniqid(mt_rand(), true), mt_rand(1, 5));
        $devPermissions = (bool) (microtime() % 2);
        $prod = (bool) (microtime() % 3);
        $mockApp = $this->getMockApp($name);

        $parentName = str_repeat(uniqid(mt_rand(), true), mt_rand(1, 5));
        $mockAppParent = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $mockAppParent->expects($this->any())->method('getNameCanonical')->willReturn($parentName);
        $mockAppParent->expects($this->any())->method('getName')->willReturn($parentName);
        $parentAppEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $parentServerPassword = uniqid();
        $parentServerSettings = new ServerSettings($parentAppEnv, $parentName, $parentServerPassword);
        $sockId = uniqid();
        $parentServerSettings->setSockAccountId($sockId);
        $parentAppEnv->expects($this->any())->method('getServerSettings')->willReturn($parentServerSettings);

        $mockAppParent->expects($this->any())->method('getAppEnvironment')->willReturn($parentAppEnv);
        $mockApp->expects($this->any())->method('getParent')->willReturn($mockAppParent);

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
        $this->assertEquals($parentServerSettings->getUser(), $appEnv->getServerSettings()->getUser());
        $this->assertEquals($parentServerPassword, $appEnv->getServerSettings()->getPassword());
        $this->assertSame($appEnv, $appEnv->getServerSettings()->getAppEnvironment());
        // Assert database settings are set.
        $this->assertInstanceOf(DatabaseSettings::class, $appEnv->getDatabaseSettings());
        $this->assertEquals($appEnv, $appEnv->getDatabaseSettings()->getAppEnvironment());
        $expectedDbName = substr($name, 0, 14) . '_' . substr($mockApp->getNameCanonical(), 0, 1);
        $this->assertSame($expectedDbName, $appEnv->getDatabaseSettings()->getName());
        $this->assertNotEmpty($appEnv->getDatabaseSettings()->getPassword());
    }

    /**
     * @dataProvider getterTestDataProvider
     */
    public function testGetter($prop, $val, $isBool = false)
    {
        $appEnv = $this->getAppEnv();
        $refObject = new ReflectionObject($appEnv);
        $refProperty = $refObject->getProperty($prop);
        $refProperty->setAccessible(true);
        $refProperty->setValue($appEnv, $val);
        $this->assertEquals($val, $appEnv->{(!$isBool ? 'get' : 'is') . ucfirst($prop)}());
    }

    /**
     * @dataProvider setterTestDataProvider
     */
    public function testSetter($prop, $val, $isBool = false)
    {
        $appEnv = $this->getAppEnv();
        $this->assertEquals($appEnv, $appEnv->{'set' . ucfirst($prop)}($val));
        $this->assertEquals($val, $appEnv->{(!$isBool ? 'get' : 'is') . ucfirst($prop)}());
    }

    public function testGetNameCanonical()
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789!@#$%^&*()_-');
        $invalidSeed = str_split('!@#$%^&*()_-');
        $name = '';

        foreach (array_rand($seed, mt_rand(5, count($seed))) as $k)
        {
            $name .= $seed[$k];
        }
        // Make sure we have at least one invalid character.
        $name .= $invalidSeed[array_rand($invalidSeed)];
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);
        $canonical = $appEnv->getNameCanonical();

        $this->assertLessThanOrEqual(strlen($name), strlen($canonical));
        $this->assertNotRegExp('/[^a-zA-Z0-9]+/', $canonical);
    }

    public function testGetFullNameCanonical()
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789!@#$%^&*()_-');
        $invalidSeed = str_split('!@#$%^&*()_-');
        $name = '';

        foreach (array_rand($seed, mt_rand(5, count($seed))) as $k)
        {
            $name .= $seed[$k];
        }
        // Make sure we have at least one invalid character.
        $name .= $invalidSeed[array_rand($invalidSeed)];
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);
        $envCanonical = $appEnv->getNameCanonical();
        $appCanonical = $mockApp->getNameCanonical();

        $this->assertEquals($appCanonical . '_' . $envCanonical, $appEnv->getFullNameCanonical());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage no domains configured
     */
    public function testPreferredDomain()
    {
        $appEnv = $this->getAppEnv();
        try
        {
            $appEnv->setDomains(['http://facebook.com', 'http://twitter.com']);
            $this->assertEquals('http://facebook.com', $appEnv->getPreferredDomain());

            $appEnv->addDomain('http://google.com', true);
            $this->assertEquals('http://google.com', $appEnv->getPreferredDomain());

            $appEnv->removeDomain('http://google.com');
            $this->assertEquals('http://facebook.com', $appEnv->getPreferredDomain());
        }
        catch (Exception $e)
        {
            $this->fail($e->getMessage());
        }

        $appEnv->setDomains([]);
        $appEnv->getPreferredDomain();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage the domain 'http://google.com' is not a configured domain for this environment.
     */
    public function testSetPreferredDomain()
    {
        $appEnv = $this->getAppEnv();
        try
        {
            $appEnv->setDomains(['http://google.com', 'http://facebook.com']);
            $appEnv->setPreferredDomain('http://google.com');
            $this->assertEquals('http://google.com', $appEnv->getPreferredDomain());
        }
        catch (\Exception $e)
        {
            $this->fail($e->getMessage());
        }
        $appEnv->setDomains([]);
        $appEnv->setPreferredDomain('http://google.com');
    }

    public function testAddDomain()
    {
        $appEnv = $this->getAppEnv();
        $appEnv->addDomain('http://google.com');
        $this->assertContains('http://google.com', $appEnv->getDomains());
        $this->assertNotContains('http://facebook.com', $appEnv->getDomains());
        $appEnv->addDomain('http://facebook.com');
        $this->assertContains('http://facebook.com', $appEnv->getDomains());
    }

    public function testRemoveDomain()
    {
        $appEnv = $this->getAppEnv();
        $appEnv->setDomains(['http://facebook.com', 'http://google.com', 'http://twitter.com']);
        $appEnv->removeDomain('http://facebook.com');
        $this->assertNotContains('http://facebook.com', $appEnv->getDomains());
        $appEnv->removeDomain('http://google.com');
        $this->assertNotContains('http://google.com', $appEnv->getDomains());
        $appEnv->removeDomain('http://twitter.com');
        $this->assertNotContains('http://twitter.com', $appEnv->getDomains());
    }

    public function testSetDomainByDefault()
    {
        $name = str_repeat(uniqid(mt_rand(), true), mt_rand(1, 5));
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);
        $mockApp->expects($this->any())->method('getNameForUrl')->willReturn($name);
        $env = $this->getMockBuilder(Environment::class)->getMock();
        $env->expects($this->any())->method('getUrlStructure')->willReturn('[URL_SCHEMA]://[APP_NAME].com');
        $appEnv->setDomainByDefault($env, 'https');
        $this->assertEquals('https://' . $name . '.com', $appEnv->getPreferredDomain());
    }

    public function testSetSiteConfig()
    {
        $appEnv = $this->getAppEnv();
        $id = uniqid();
        $config = $id . "\n\r";
        $appEnv->setSiteConfig($config);
        $this->assertEquals($id . "\n", $appEnv->getSiteConfig());
    }

    public function testReplaceConfigPlaceholders()
    {
        $appEnv = $this->getAppEnv();
        $appEnv->setDomains(['http://google.com']);
        $appEnv->setPreferredDomain('http://google.com');
        $content = '[[URL]] [[IP]]';
        $ip = uniqid();

        $server = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $server->expects($this->once())->method('isTaskServer')->willReturn(true);
        $server->expects($this->once())->method('getEnvironment')->willReturn($appEnv->getNameCanonical());
        $server->expects($this->once())->method('getIp')->willReturn($ip);

        $servers = [$server];
        $this->assertEquals('http://google.com ' . $ip, $appEnv->replaceConfigPlaceholders($content, $servers));
    }

    public function testGetProjectSpecificDirs()
    {
        $appEnv = $this->getAppEnv();
        $mockApp = $appEnv->getApplication();
        $type = $this->getMockBuilder(ApplicationType::class)->disableOriginalConstructor()->getMock();
        $type->expects($this->any())->method('getSlug')->willReturn(uniqid());
        $dirs = [uniqid(), uniqid()];
        $user = uniqid();
        $type->expects($this->once())->method('getDirectories')->with($mockApp, $user)->willReturn($dirs);
        $mockApp->expects($this->any())->method('getType')->willReturn($type);
        $this->assertEquals($dirs, $appEnv->getProjectSpecificDirs($user));
    }

    public function testToString()
    {
        $appEnv = $this->getAppEnv();
        $this->assertEquals($appEnv->getName(), (string) $appEnv);
    }

    protected function getMockApp($name)
    {
        $mockApp = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $mockApp->expects($this->any())->method('getCron')->willReturn('cron_' . $name);
        $mockApp->expects($this->any())->method('hasDatabase')->willReturn(true);
        $mockApp->expects($this->any())->method('getNameCanonical')->willReturn($name);
        $mockApp->expects($this->any())->method('getName')->willReturn($name);

        return $mockApp;
    }

    protected function getAppEnv()
    {
        $name = str_repeat(uniqid(mt_rand(), true), mt_rand(1, 5));
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);

        return $appEnv;
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['salt', uniqid()],
            ['application', $this->getMockApp(uniqid())],
            ['sockApplicationId', uniqid()],
            ['name', uniqid()],
            ['preferredDomain', 'http://google.com'],
            ['gitRef', uniqid()],
            ['cron', uniqid()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['sshKeyGroups', new ArrayCollection([new SshKeyGroup(uniqid()), new SshKeyGroup(uniqid())])],
            ['ciJobUri', uniqid()],
            ['devPermissions', true, true],
            ['devPermissions', false, true],
            ['prod', true, true],
            ['prod', false, true],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['application', $this->getMockApp(uniqid())],
            ['sockApplicationId', uniqid()],
            ['gitRef', uniqid()],
            ['siteConfig', uniqid()],
            ['cron', uniqid()],
            ['sshKeyGroups', new ArrayCollection([new SshKeyGroup(uniqid()), new SshKeyGroup(uniqid())])],
            ['databaseSettings', new DatabaseSettings($this->getAppEnv(), uniqid())],
            ['ciJobUri', uniqid()],
            ['devPermissions', true, true],
            ['devPermissions', false, true],
            ['prod', true, true],
            ['prod', false, true],
        ];
    }

}

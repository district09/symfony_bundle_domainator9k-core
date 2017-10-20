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
use InvalidArgumentException;
use ReflectionObject;

/**
 * Description of AppEnvironmentTest
 *
 * @author Jelle Sebreghts
 */
class AppEnvironmentTest extends EntityTest
{

    public function testContstructor()
    {
        $name = str_repeat($this->getAlphaNumeric(false, 13), mt_rand(1, 5));
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
        $name = str_repeat($this->getAlphaNumeric(false, 13), mt_rand(1, 5));
        $devPermissions = (bool) (microtime() % 2);
        $prod = (bool) (microtime() % 3);
        $mockApp = $this->getMockApp($name);

        $parentName = str_repeat($this->getAlphaNumeric(false, 13), mt_rand(1, 5));
        $mockAppParent = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $mockAppParent->expects($this->any())->method('getNameCanonical')->willReturn($parentName);
        $mockAppParent->expects($this->any())->method('getName')->willReturn($parentName);
        $parentAppEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $parentServerPassword = $this->getAlphaNumeric();
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
        $name = $this->getAlphaNumeric(true);
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);
        $canonical = $appEnv->getNameCanonical();

        $this->assertLessThanOrEqual(strlen($name), strlen($canonical));
        $this->assertNotRegExp('/[^a-zA-Z0-9]+/', $canonical);
    }

    public function testGetFullNameCanonical()
    {
        $name = $this->getAlphaNumeric(true);
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
     * @expectedException InvalidArgumentException
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
        $name = str_repeat($this->getAlphaNumeric(false, 13), mt_rand(1, 5));
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);
        $mockApp->expects($this->any())->method('getNameForUrl')->willReturn($name);
        $env = $this->getMockBuilder(Environment::class)->getMock();
        $env->expects($this->any())->method('getUrlStructure')->willReturn('http://[APP_NAME].[URL_SCHEMA]');
        $appEnv->setDomainByDefault($env, 'com');
        $this->assertEquals('http://' . $name . '.com', $appEnv->getPreferredDomain());
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
        $ip = $this->getAlphaNumeric();

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
        $type->expects($this->any())->method('getSlug')->willReturn($this->getAlphaNumeric());
        $dirs = [$this->getAlphaNumeric(), $this->getAlphaNumeric()];
        $user = $this->getAlphaNumeric();
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
        $name = str_repeat($this->getAlphaNumeric(false, 13), mt_rand(1, 5));
        $mockApp = $this->getMockApp($name);
        $appEnv = new AppEnvironment($mockApp, $name, false, true);

        return $appEnv;
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['salt', $this->getAlphaNumeric(true)],
            ['application', $this->getMockApp($this->getAlphaNumeric())],
            ['sockApplicationId', uniqid()],
            ['name', $this->getAlphaNumeric()],
            ['preferredDomain', 'http://google.com'],
            ['gitRef', $this->getAlphaNumeric()],
            ['cron', $this->getAlphaNumeric()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['sshKeyGroups', new ArrayCollection([new SshKeyGroup($this->getAlphaNumeric()), new SshKeyGroup($this->getAlphaNumeric())])],
            ['ciJobUri', $this->getAlphaNumeric()],
            ['devPermissions', true, true],
            ['devPermissions', false, true],
            ['prod', true, true],
            ['prod', false, true],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['application', $this->getMockApp($this->getAlphaNumeric())],
            ['sockApplicationId', uniqid()],
            ['gitRef', $this->getAlphaNumeric()],
            ['siteConfig', $this->getAlphaNumeric()],
            ['cron', $this->getAlphaNumeric()],
            ['sshKeyGroups', new ArrayCollection([new SshKeyGroup($this->getAlphaNumeric()), new SshKeyGroup($this->getAlphaNumeric())])],
            ['databaseSettings', new DatabaseSettings($this->getAppEnv(), $this->getAlphaNumeric())],
            ['ciJobUri', $this->getAlphaNumeric()],
            ['devPermissions', true, true],
            ['devPermissions', false, true],
            ['prod', true, true],
            ['prod', false, true],
        ];
    }

    protected function getEntity()
    {
        return $this->getAppEnv();
    }

}

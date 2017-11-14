<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use Ctrl\RadBundle\Entity\User;
use DateTime;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Entity\DatabaseSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionObject;

/**
 * Description of AppEnvironmentSettingsTest.
 *
 * @author Jelle Sebreghts
 */
class ApplicationTest extends EntityTest
{
    /**
     * @var BaseAppType|PHPUnit_Framework_MockObject_MockObject
     */
    protected $type;

    /**
     * @var Environment[]|PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $environments;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $siteConfig;

    protected function setUp()
    {
        parent::setUp();
        $this->name = $this->getAlphaNumeric(true);
        $this->scheme = $this->getAlphaNumeric();
        $this->siteConfig = $this->getAlphaNumeric();
        $this->type = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();
        $this->type->expects($this->any())->method('getSlug')->willReturn($this->getAlphaNumeric());
        $env = $this->getMockBuilder(Environment::class)->getMock();
        $env->expects($this->atLeastOnce())->method('getName')->willReturn($this->getAlphaNumeric());
        $env->expects($this->atLeastOnce())->method('isDevPermissions')->willReturn((bool) mt_rand(0, 1));
        $env->expects($this->atLeastOnce())->method('isProd')->willReturn((bool) mt_rand(0, 1));
        $this->environments = [
            $env,
        ];
    }

    public function testContstructor()
    {
        $app = $this->getEntity();
        $this->assertEquals($this->type->getSlug(), $app->getAppTypeSlug());
        $this->assertEquals($this->name, $app->getName());
        $this->assertNotRegExp('/[^a-zA-Z0-9]+/', $app->getNameCanonical());
        $this->assertInstanceOf(DateTime::class, $app->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $app->getUpdatedAt());
        $this->assertInstanceOf(ArrayCollection::class, $app->getAppEnvironments());
        $this->assertInstanceOf(ArrayCollection::class, $app->getRoles());
        $this->assertInstanceOf(ArrayCollection::class, $app->getUsers());
        $this->assertEmpty($app->getParent());
    }

    public function testContstructorWithParent()
    {
        $parent = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $parentEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $parentEnv->expects($this->any())->method('getServerSettings')->willReturn($this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock());
        $parent->expects($this->any())->method('getAppEnvironment')->willReturn($parentEnv);
        $app = new Application($this->type, $this->name, $this->environments, $this->scheme, $this->siteConfig, $parent);
        $this->assertEquals($parent, $app->getParent());
    }

    public function testGetAppTypeSlug()
    {
        $app = $this->getEntity();
        $this->assertEquals($this->type->getSlug(), $app->getAppTypeSlug());

        // Set a different type and make sure we get that slug.
        $type = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();
        $type->expects($this->any())->method('getSlug')->willReturn($this->getAlphaNumeric());
        $refObject = new ReflectionObject($app);
        $refProperty = $refObject->getProperty('type');
        $refProperty->setAccessible(true);
        $refProperty->setValue($app, $type);
        $this->assertEquals($type->getSlug(), $app->getAppTypeSlug());
    }

    public function testGetNameForUrl()
    {
        $app = $this->getEntity();
        $urlName = $app->getNameForUrl();
        $this->assertLessThanOrEqual(strlen($app->getName()), strlen($urlName));
        $this->assertNotRegExp('/[^a-z0-9-]+/', $urlName);
    }

    public function testGetNameForFolder()
    {
        $app = $this->getEntity();
        $this->assertEquals('default', $app->getNameForFolder());
        $parent = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->setParent($parent);
        $folderName = $app->getNameForFolder();
        $this->assertLessThanOrEqual(strlen($app->getName()), strlen($folderName));
        $this->assertNotRegExp('/[^a-zA-Z0-9-]+/', $folderName);
        $this->assertLessThanOrEqual(14, strlen($folderName));
    }

    public function testGetGitRepoFull()
    {
        $app = $this->getEntity();
        $repo = $this->getAlphaNumeric();
        $app->setGitRepo($repo);
        $this->assertEquals('git@bitbucket.org:' . $repo . '.git', $app->getGitRepoFull());
    }

    public function testSetHasDatabase()
    {
        $app = $this->getEntity();
        $dbSettings = $this->getMockBuilder(DatabaseSettings::class)->disableOriginalConstructor()->getMock();
        $dbSettings->expects($this->any())->method('isCreated')->will($this->onConsecutiveCalls(true, true, false, false));
        $appEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnv->expects($this->any())->method('getDatabaseSettings')->willReturn($dbSettings);
        $appEnv->expects($this->once())->method('setDatabaseSettings')->with(null);
        $appEnv->expects($this->exactly(3))->method('assertDatabaseSettings');

        $appEnvCollection = new ArrayCollection([$appEnv]);

        $refObject = new ReflectionObject($app);
        $refProperty = $refObject->getProperty('appEnvironments');
        $refProperty->setAccessible(true);
        $refProperty->setValue($app, $appEnvCollection);

        // If the db is already created, hasDatabase is forced to true.
        $this->assertEquals($app, $app->setHasDatabase(false));
        $this->assertTrue($app->hasDatabase());

        $this->assertEquals($app, $app->setHasDatabase(true));
        $this->assertTrue($app->hasDatabase());

        // No db created yet.
        $this->assertEquals($app, $app->setHasDatabase(false));
        $this->assertFalse($app->hasDatabase());

        $this->assertEquals($app, $app->setHasDatabase(true));
        $this->assertTrue($app->hasDatabase());
    }

    public function testGetAppEnvironments()
    {
        $app = $this->getEntity();
        $appEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $roles = [$this->getAlphaNumeric()];
        $user->expects($this->any())->method('getRoles')->willReturn($roles);
        $appEnv->expects($this->any())->method('getNameCanonical')->will($this->onConsecutiveCalls('test', $this->getAlphaNumeric(), $this->getAlphaNumeric(), $this->getAlphaNumeric()));
        $appEnv->expects($this->any())->method('hasUser')->with($user)->will($this->onConsecutiveCalls(true, false, false));
        $appEnv->expects($this->any())->method('hasAnyRole')->with($roles)->will($this->onConsecutiveCalls(true, false));

        $appEnvCollection = new ArrayCollection([$appEnv]);

        $refObject = new ReflectionObject($app);
        $refProperty = $refObject->getProperty('appEnvironments');
        $refProperty->setAccessible(true);
        $refProperty->setValue($app, $appEnvCollection);

        $this->assertEquals($appEnvCollection, $app->getAppEnvironments($user));
        $this->assertEquals($appEnvCollection, $app->getAppEnvironments($user));
        $this->assertEquals($appEnvCollection, $app->getAppEnvironments($user));
        $this->assertEmpty($app->getAppEnvironments($user));
        $this->assertEquals($appEnvCollection, $app->getAppEnvironments());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Application has no environment 'test1234'
     */
    public function testGetAppEnvironment()
    {
        $app = $this->getEntity();
        $env = $this->environments[0];
        $appEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnv->expects($this->any())->method('getName')->willReturn($env->getName());
        $appEnv->expects($this->any())->method('getNameCanonical')->willReturn($env->getName());

        $appEnvCollection = new ArrayCollection([$appEnv]);

        $refObject = new ReflectionObject($app);
        $refProperty = $refObject->getProperty('appEnvironments');
        $refProperty->setAccessible(true);
        $refProperty->setValue($app, $appEnvCollection);

        try {
            $this->assertEquals($appEnv, $app->getAppEnvironment($env->getName()));
            $this->assertEquals($appEnv, $app->getAppEnvironment($env));
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        $app->getAppEnvironment('test1234');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Application has no environment marked as isProd == true
     */
    public function testGetProdAppEnvironment()
    {
        $app = $this->getEntity();
        $appEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnv->expects($this->at(0))->method('isProd')->willReturn(true);
        $appEnv->expects($this->at(0))->method('isProd')->willReturn(false);

        $appEnvCollection = new ArrayCollection([$appEnv]);

        $refObject = new ReflectionObject($app);
        $refProperty = $refObject->getProperty('appEnvironments');
        $refProperty->setAccessible(true);
        $refProperty->setValue($app, $appEnvCollection);

        try {
            $this->assertEquals($appEnv, $app->getProdAppEnvironment());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        $app->getProdAppEnvironment();
    }

    public function testAddAppEnvironment()
    {
        $app = $this->getEntity();
        $appEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnv->expects($this->once())->method('setApplication')->with($app);

        $this->assertEquals($app, $app->addAppEnvironment($appEnv));
        $this->assertEquals($appEnv, $app->getAppEnvironments()->last());
    }

    public function testToString()
    {
        $app = $this->getEntity();
        $this->assertEquals($app->getName(), (string) $app);
    }

    public function testAllowPartialBuilds()
    {
        $app = $this->getEntity();
        $build = $this->getMockBuilder(Build::class)->disableOriginalConstructor()->getMock();
        $build->expects($this->any())->method('isCompleted')->will($this->onConsecutiveCalls(false, true, true));
        $build->expects($this->any())->method('getSuccess')->will($this->onConsecutiveCalls(false, true));

        $refObject = new ReflectionObject($app);
        $refProperty = $refObject->getProperty('builds');
        $refProperty->setAccessible(true);
        $refProperty->setValue($app, [$build]);

        $this->assertFalse($app->allowPartialBuilds());
        $this->assertFalse($app->allowPartialBuilds());
        $this->assertTrue($app->allowPartialBuilds());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['name', $this->getAlphaNumeric()],
            ['nameCanonical', $this->getAlphaNumeric()],
            ['parent', $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock()],
            ['children', [$this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock()]],
            ['type', $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock()],
            ['gitRepo', $this->getAlphaNumeric()],
            ['hasDatabase', (bool) mt_rand(0, 1), true, ''],
            ['hasSolr', (bool) mt_rand(0, 1), true, ''],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['provisionBuild', uniqid()],
            ['dnsMailSent', (bool) mt_rand(0, 1), true],
            ['dnsMailTemplate', $this->getAlphaNumeric()],
            ['cron', $this->getAlphaNumeric()],
            ['ciTypeSlug', $this->getAlphaNumeric()],
            ['appTypeSettings', $this->getAlphaNumeric()],
            ['ciAppTypeSettings', $this->getAlphaNumeric()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['appTypeSlug', $this->getAlphaNumeric()],
            ['name', $this->getAlphaNumeric()],
            ['parent', $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock()],
            ['type', $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock()],
            ['gitRepo', $this->getAlphaNumeric()],
            ['hasSolr', (bool) mt_rand(0, 1), true, ''],
            ['provisionBuild', uniqid()],
            ['dnsMailSent', (bool) mt_rand(0, 1), true],
            ['dnsMailTemplate', $this->getAlphaNumeric()],
            ['cron', $this->getAlphaNumeric()],
            ['ciTypeSlug', $this->getAlphaNumeric()],
            ['appTypeSettings', $this->getAlphaNumeric()],
            ['ciAppTypeSettings', $this->getAlphaNumeric()],
        ];
    }

    /**
     * @return Application
     */
    protected function getEntity()
    {
        return new Application($this->type, $this->name, $this->environments, $this->scheme, $this->siteConfig);
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ServerSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\AppEnvironmentService;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use DigipolisGent\SockAPIBundle\JsonModel\Account;
use DigipolisGent\SockAPIBundle\Service\AccountService;
use DigipolisGent\SockAPIBundle\Service\Promise\EntityCreatePromise;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of AppEnvironmentServiceTest
 *
 * @author Jelle Sebreghts
 */
class AppEnvironmentServiceTest extends TestCase
{
    use DataGenerator;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $settings;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $applicationTypeBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();
        $this->applicationTypeBuilder = $this->getMockBuilder(ApplicationTypeBuilder::class)->getMock();
    }

    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(AppEnvironment::class, $service->getEntityClass());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateSockAccountNoSockId() {
        $service = $this->getService();
        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $server = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $server->expects($this->once())->method('getSockId')->willReturn(NULL);
        $sockAccountService = $this->getMockBuilder(AccountService::class)->disableOriginalConstructor()->getMock();
        $service->createSockAccount($appEnvironment, $server, $sockAccountService);
    }

    public function testCreateSockAccountExists() {
        $service = $this->getService();

        $user = $this->getAlphaNumeric();

        $serverSettings = $this->getMockBuilder(ServerSettings::class)->disableOriginalConstructor()->getMock();
        $serverSettings->expects($this->any())->method('getUser')->willReturn($user);

        $appEnvironment = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $appEnvironment->expects($this->any())->method('getServerSettings')->willReturn($serverSettings);

        $sockId = uniqid();

        $server = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $server->expects($this->any())->method('getSockId')->willReturn($sockId);

        $account = $this->getMockBuilder(Account::class)->getMock();
        $account->expects($this->any())->method('getId')->willReturn($sockId);

        $sockAccountService = $this->getMockBuilder(AccountService::class)->disableOriginalConstructor()->getMock();
        $sockAccountService->expects($this->once())->method('findByName')->with($user, $sockId)->willReturn($account);

        $serverSettings->expects($this->once())->method('setSockAccountId')->with($sockId);

        $promise = $service->createSockAccount($appEnvironment, $server, $sockAccountService);

        $this->assertInstanceOf(EntityCreatePromise::class, $promise);
        $this->assertTrue($promise->getIsResolved());
        $this->assertTrue($promise->getIsCreated());
        $this->assertTrue($promise->getDidExist());
    }


    /**
     *
     * @return AppEnvironmentService
     */
    protected function getService()
    {
        return new AppEnvironmentService($this->settings, $this->applicationTypeBuilder);
    }

}

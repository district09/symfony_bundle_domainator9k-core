<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Security\Voter;

use Ctrl\RadBundle\Entity\User;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Security\Voter\ApplicationVoter;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Description of ApplicationVoterTest
 *
 * @author Jelle Sebreghts
 */
class ApplicationVoterTest extends TestCase
{

    use DataGenerator;

    protected $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports($attribute, $app, $expected)
    {
        $voter = $this->getVoter();
        $class = new ReflectionClass($voter);
        $method = $class->getMethod('supports');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invokeArgs($voter, [$attribute, $app]));
    }

    public function testVoteOnAttribute()
    {
        $voter = $this->getVoter();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();

        $class = new ReflectionClass($voter);
        $method = $class->getMethod('voteOnAttribute');
        $method->setAccessible(true);

        $roles = [$this->getAlphaNumeric()];

        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $app->expects($this->any())->method('hasUser')->with($user)->willReturnOnConsecutiveCalls(true, false, false);
        $app->expects($this->any())->method('getRoles')->with(true)->willReturn($roles);

        $token = $this->getMockBuilder(TokenInterface::class)->disableOriginalConstructor()->getMock();
        $token->expects($this->any())->method('isAuthenticated')->willReturnOnConsecutiveCalls(false, true, true, true, true);
        $token->expects($this->any())->method('getUser')->willReturn($user);

        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $authChecker->expects($this->at(1))->method('isGranted')->with(['ROLE_ADMIN'], $user)->willReturn(true);
        $authChecker->expects($this->at(2))->method('isGranted')->with(['ROLE_ADMIN'], $user)->willReturn(false);
        $authChecker->expects($this->at(3))->method('isGranted')->with($roles, $user)->willReturn(true);
        $authChecker->expects($this->at(4))->method('isGranted')->with(['ROLE_ADMIN'], $user)->willReturn(false);
        $authChecker->expects($this->at(5))->method('isGranted')->with($roles, $user)->willReturn(false);

        $this->container->expects($this->any())->method('get')->with('security.authorization_checker')->willReturn($authChecker);

        $attribute = ApplicationVoter::EDIT;

        $this->assertFalse($method->invokeArgs($voter, [$attribute, $app, $token]));
        $this->assertTrue($method->invokeArgs($voter, [$attribute, $app, $token]));
        $this->assertTrue($method->invokeArgs($voter, [$attribute, $app, $token]));
        $this->assertTrue($method->invokeArgs($voter, [$attribute, $app, $token]));
        $this->assertFalse($method->invokeArgs($voter, [$attribute, $app, $token]));
    }

    public function supportsDataProvider()
    {
        return [
            [ApplicationVoter::EDIT, $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock(), true],
            [ApplicationVoter::EDIT_RIGHTS, $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock(), true],
            [ApplicationVoter::PROVISION, $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock(), true],
            [ApplicationVoter::VIEW, $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock(), true],
            [$this->getAlphaNumeric(), $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock(), false],
            [ApplicationVoter::EDIT, $this->getAlphaNumeric(), false],
            [ApplicationVoter::EDIT_RIGHTS, $this->getAlphaNumeric(), false],
            [ApplicationVoter::PROVISION, $this->getAlphaNumeric(), false],
            [ApplicationVoter::VIEW, $this->getAlphaNumeric(), false],
            [$this->getAlphaNumeric(), $this->getAlphaNumeric(), false],
        ];
    }

    /**
     *
     * @return ApplicationVoter
     */
    protected function getVoter()
    {
        return new ApplicationVoter($this->container);
    }

}

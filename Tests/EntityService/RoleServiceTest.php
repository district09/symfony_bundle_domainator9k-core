<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EntityService;

use Ctrl\Common\EntityService\Finder\Doctrine\Finder;
use Ctrl\Common\EntityService\Finder\Doctrine\Result;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Role;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\RoleService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\ORM\EntityManager;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of RoleServiceTest
 *
 * @author Jelle Sebreghts
 */
class RoleServiceTest extends TestCase
{

    use DataGenerator;

    /**
     *
     * @var array
     */
    protected $roleHierarchy;

    /**
     *
     * @var
     */
    protected $doctrine;

    protected function setUp()
    {
        parent::setUp();
        $this->roleHierarchy = [$this->getAlphaNumeric(), $this->getAlphaNumeric()];
        $this->doctrine = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
    }

    public function testGetEntityClass()
    {
        $service = $this->getService();
        $this->assertEquals(Role::class, $service->getEntityClass());
    }

    public function testGetSetRoleHierarchy() {
        $service = $this->getService();
        $this->assertEquals($this->roleHierarchy, $service->getRoleHierarchy());
        $newHierarchy = [$this->getAlphaNumeric(), $this->getAlphaNumeric()];
        $service->setRoleHierarchy($newHierarchy);
        $this->assertEquals($newHierarchy, $service->getRoleHierarchy());
    }

    public function testGetRole() {
        $service = $this->getService();

        $name = $this->getAlphaNumeric();

        $role = $this->getMockBuilder(Role::class)->disableOriginalConstructor()->getMock();

        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('getFirstOrNull')->willReturn($role);

        $finder = $this->getMockBuilder(Finder::class)->disableOriginalConstructor()->getMock();
        $finder->expects($this->once())->method('find')->with(['name' => $name])->willReturn($result);

        $refObject = new ReflectionObject($service);
        $refProperty = $refObject->getProperty('finder');
        $refProperty->setAccessible(true);
        $refProperty->setValue($service, $finder);

        $this->doctrine->expects($this->never())->method('persist');

        $this->assertEquals($role, $service->getOrCreateRole($name));
    }

    public function testCreateRole() {
        $service = $this->getService();

        $name = $this->getAlphaNumeric();

        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('getFirstOrNull')->willReturn(null);

        $finder = $this->getMockBuilder(Finder::class)->disableOriginalConstructor()->getMock();
        $finder->expects($this->once())->method('find')->with(['name' => $name])->willReturn($result);

        $refObject = new ReflectionObject($service);
        $refProperty = $refObject->getProperty('finder');
        $refProperty->setAccessible(true);
        $refProperty->setValue($service, $finder);

        $this->doctrine->expects($this->once())->method('persist')->with($this->callback(function (Role $role) use ($name) {
            return $role->getName() === $name;
        }));

        $this->assertEquals($name, $service->getOrCreateRole($name)->getName());
    }

    /**
     *
     * @return RoleService
     */
    protected function getService()
    {
        $service = new RoleService($this->roleHierarchy);
        $service->setDoctrine($this->doctrine);
        return $service;
    }

}

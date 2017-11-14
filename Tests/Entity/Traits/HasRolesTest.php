<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Traits;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Role;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasRoles;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HasRolesTest.
 *
 * @author Jelle Sebreghts
 */
class HasRolesTest extends TestCase
{
    /**
     * @var HasRoles|PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockedTrait;

    /**
     * @var Role[]
     */
    protected $roles;

    public function setUp()
    {
        parent::setUp();
        $this->mockedTrait = $this->getMockBuilder(HasRoles::class)->getMockForTrait();
        $this->roles = [
            new Role('role1'),
            new Role('role2'),
            new Role('role3'),
        ];
    }

    public function testGetRoles()
    {
        $result = $this->mockedTrait->setRoles($this->roles);
        $this->assertEquals($this->roles, $this->mockedTrait->getRoles()->toArray());
        $this->assertEquals(['role1', 'role2', 'role3'], $this->mockedTrait->getRoles(true));
        $this->assertEquals($result, $this->mockedTrait);
    }

    public function testSetRoles()
    {
        $result = $this->mockedTrait->setRoles($this->roles);
        $this->assertEquals($this->roles, $this->mockedTrait->getRoles()->toArray());
        $this->assertEquals($result, $this->mockedTrait);

        $result = $this->mockedTrait->setRoles(new ArrayCollection($this->roles));
        $this->assertEquals($this->roles, $this->mockedTrait->getRoles()->toArray());
        $this->assertEquals($result, $this->mockedTrait);
    }

    public function testAddRole()
    {
        foreach ($this->roles as $role) {
            $result = $this->mockedTrait->addRole($role);
            $this->assertEquals($result, $this->mockedTrait);
        }
        $this->assertEquals($this->roles, $this->mockedTrait->getRoles()->toArray());

        // Test that adding them again doesn't duplicate them.
        foreach ($this->roles as $role) {
            $result = $this->mockedTrait->addRole($role);
            $this->assertEquals($result, $this->mockedTrait);
        }
        $this->assertEquals($this->roles, $this->mockedTrait->getRoles()->toArray());
    }

    public function testRemoveRole()
    {
        $this->mockedTrait->setRoles($this->roles);
        $this->mockedTrait->removeRole('role2');
        $this->assertEquals(['role1', 'role3'], $this->mockedTrait->getRoles(true));

        $this->mockedTrait->setRoles($this->roles);
        $this->mockedTrait->removeRole($this->roles[1]);
        $this->assertEquals(['role1', 'role3'], $this->mockedTrait->getRoles(true));
    }

    public function testHasRole()
    {
        $this->mockedTrait->setRoles($this->roles);
        $this->assertTrue($this->mockedTrait->hasRole('role1'));
        $this->assertTrue($this->mockedTrait->hasRole('role2'));
        $this->assertTrue($this->mockedTrait->hasRole('role3'));
        $this->assertFalse($this->mockedTrait->hasRole('role4'));

        $this->assertTrue($this->mockedTrait->hasRole($this->roles[0]));
        $this->assertTrue($this->mockedTrait->hasRole($this->roles[1]));
        $this->assertTrue($this->mockedTrait->hasRole($this->roles[2]));
        $this->assertFalse($this->mockedTrait->hasRole(new Role('role4')));
    }

    public function testHasAnyRole()
    {
        $this->mockedTrait->setRoles($this->roles);
        $this->assertTrue($this->mockedTrait->hasAnyRole(['role1', 'role4']));
        $this->assertTrue($this->mockedTrait->hasAnyRole([$this->roles[0], 'role4']));
        $this->assertTrue($this->mockedTrait->hasAnyRole([$this->roles[0], new Role('role4')]));
        $this->assertTrue($this->mockedTrait->hasAnyRole(['role1', new Role('role4')]));

        $this->assertFalse($this->mockedTrait->hasAnyRole(['role4', 'role5']));
        $this->assertFalse($this->mockedTrait->hasAnyRole([new Role('role4'), 'role5']));
        $this->assertFalse($this->mockedTrait->hasAnyRole([new Role('role4'), new Role('role5')]));
        $this->assertFalse($this->mockedTrait->hasAnyRole(['role4', new Role('role5')]));
    }
}

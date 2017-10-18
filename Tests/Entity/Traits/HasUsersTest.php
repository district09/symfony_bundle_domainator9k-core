<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Traits;

use Ctrl\RadBundle\Entity\User;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\HasUsers;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HasUsersTest
 *
 * @author Jelle Sebreghts
 */
class HasUsersTest extends TestCase
{
    /**
     *
     * @var HasUsers|PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockedTrait;

    /**
     *
     * @var User[]
     */
    protected $users;

    public function setUp()
    {
        parent::setUp();
        $this->mockedTrait = $this->getMockBuilder(HasUsers::class)->getMockForTrait();
        $this->users = [
            (new User())->setUsername('user1'),
            (new User())->setUsername('user2'),
            (new User())->setUsername('user3'),
        ];
    }

    public function testGetUsers() {
        $result = $this->mockedTrait->setUsers($this->users);
        $this->assertEquals($this->users, $this->mockedTrait->getUsers()->toArray());
        $this->assertEquals($result, $this->mockedTrait);
    }

    public function testSetUsers() {
        $result = $this->mockedTrait->setUsers($this->users);
        $this->assertEquals($this->users, $this->mockedTrait->getUsers()->toArray());
        $this->assertEquals($result, $this->mockedTrait);

        $result = $this->mockedTrait->setUsers(new ArrayCollection($this->users));
        $this->assertEquals($this->users, $this->mockedTrait->getUsers()->toArray());
        $this->assertEquals($result, $this->mockedTrait);
    }

    public function testAddUser() {
        foreach ($this->users as $user) {
            $result = $this->mockedTrait->addUser($user);
            $this->assertEquals($result, $this->mockedTrait);
        }
        $this->assertEquals($this->users, $this->mockedTrait->getUsers()->toArray());
    }

    public function testRemoveUser() {
        $this->mockedTrait->setUsers($this->users);
        $this->mockedTrait->removeUser($this->users[1]);
        $this->assertEquals([$this->users[0], $this->users[2]], array_values($this->mockedTrait->getUsers()->toArray()));
    }

    public function testHasUser() {
        $this->mockedTrait->setUsers($this->users);
        $this->assertTrue($this->mockedTrait->hasUser($this->users[0]));
        $this->assertTrue($this->mockedTrait->hasUser($this->users[1]));
        $this->assertTrue($this->mockedTrait->hasUser($this->users[2]));
        $this->assertFalse($this->mockedTrait->hasUser(new User()));
    }

}

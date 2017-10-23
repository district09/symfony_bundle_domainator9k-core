<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Role;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;

/**
 * Description of AppEnvironmentSettingsTest
 *
 * @author Jelle Sebreghts
 */
class RoleTest extends EntityTest
{
    protected $name;

    protected function setUp()
    {
        parent::setUp();
        $this->name = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $role = $this->getEntity();
        $this->assertEquals($this->name, $role->getName());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['name', $this->getAlphaNumeric()],
        ];
    }

    public function testGetRole() {
        $role = $this->getEntity();
        $this->assertEquals($role->getName(), $role->getRole());
        $this->assertEquals($this->name, $role->getRole());
    }

    public function setterTestDataProvider()
    {
        return [
            ['name', $this->getAlphaNumeric()],
        ];
    }

    /**
     *
     * @return Role
     */
    protected function getEntity()
    {
        return new Role($this->name);
    }

}

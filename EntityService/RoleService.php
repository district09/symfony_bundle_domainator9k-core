<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class RoleService extends AbstractDoctrineService
{
    /**
     * @var array
     */
    protected $roleHierarchy = [];

    /**
     * @param $roleHierarchy
     */
    public function __construct($roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return Role::class;
    }

    /**
     * @param string $name
     *
     * @return Role
     */
    public function getOrCreateRole($name)
    {
        $role = $this->getFinder()->find(['name' => $name])->getFirstOrNull();

        if (!$role) {
            $role = new Role($name);
            $this->persist($role);
        }

        return $role;
    }

    /**
     * @return RoleHierarchy
     */
    public function getRoleHierarchy()
    {
        return $this->roleHierarchy;
    }

    /**
     * @param RoleHierarchy $roleHierarchy
     *
     * @return $this
     */
    public function setRoleHierarchy($roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;

        return $this;
    }
}

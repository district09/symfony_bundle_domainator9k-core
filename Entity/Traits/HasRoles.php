<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Traits;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;

trait HasRoles
{
    /**
     * @var Role[]|ArrayCollection|array
     * @ORM\ManyToMany(targetEntity="\DigipolisGent\Domainator9k\CoreBundle\Entity\Role")
     */
    protected $roles = array();

    /**
     * Gets the roles.
     *
     * @param bool $asStrings
     *
     * @return array|Role[]|ArrayCollection
     */
    public function getRoles($asStrings = false)
    {
        if ($asStrings) {
            $roles = [];
            foreach ($this->roles as $role) {
                $roles[] = $role->getRole();
            }

            return $roles;
        }

        return $this->roles;
    }

    /**
     * Sets the roles.
     *
     * @param array|Role[]|ArrayCollection $roles
     *
     * @return $this
     */
    public function setRoles($roles)
    {
        $this->roles = ($roles instanceof ArrayCollection)
            ? $roles
            : new ArrayCollection($roles);

        return $this;
    }

    /**
     * Adds a role.
     *
     * @param Role $role
     *
     * @return $this
     */
    public function addRole(Role $role)
    {
        if (!$this->roles instanceof ArrayCollection) {
            $this->roles = new ArrayCollection($this->roles);
        }
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Removes a role.
     *
     * @param string|Role $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $this->roles->removeElement($role);
        } else {
            foreach ($this->roles as $r) {
                if ($r->getRole() === $role) {
                    $this->roles->removeElement($r);

                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Checks if this entity has a role.
     *
     * @param string|Role $role
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            foreach ($this->roles as $r) {
                if ($r->getName() === $role) {
                    return true;
                }
            }

            return false;
        }

        return $this->roles->contains($role);
    }

    /**
     * Checks if this entity has any of the given roles.
     *
     * @param string[]|Role[] $roles
     *
     * @return boolean
     */
    public function hasAnyRole($roles)
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}

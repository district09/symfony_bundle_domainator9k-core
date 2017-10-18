<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Traits;

use Ctrl\RadBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

trait HasUsers
{
    /**
     * @var User[]|ArrayCollection|array
     * @ORM\ManyToMany(targetEntity="\Ctrl\RadBundle\Entity\User")
     */
    protected $users = array();

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     *
     * @return $this
     */
    public function setUsers($users)
    {
        $this->users = ($users instanceof ArrayCollection)
            ? $users
            : new ArrayCollection($users);

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addUser(User $user)
    {
        if (!$this->users instanceof ArrayCollection) {
            $this->users = new ArrayCollection($this->users);
        }
        $this->users->add($user);

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }
}

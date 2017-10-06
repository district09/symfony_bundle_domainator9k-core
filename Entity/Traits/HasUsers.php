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
        $this->users = $users;

        return $this;
    }

    public function addUser(User $user)
    {
        $this->users[] = $user;
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }
}

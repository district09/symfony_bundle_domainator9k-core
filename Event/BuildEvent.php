<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Event;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use Symfony\Component\EventDispatcher\Event;

class BuildEvent extends Event
{

    protected $build;

    const NAME = 'domainator.build';

    public function __construct(Build $build)
    {
        $this->build = $build;
    }

    public function getBuild(){
        return $this->build;
    }

}
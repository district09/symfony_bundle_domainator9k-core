<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;

class QuuxApplication extends AbstractApplication
{
    public static function getType()
    {
        return 'quux_application';
    }

}
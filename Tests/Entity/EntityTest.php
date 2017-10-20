<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of EnittyTest
 *
 * @author Jelle Sebreghts
 */
abstract class EntityTest extends TestCase
{

    /**
     * @dataProvider getterTestDataProvider
     */
    public function testGetter($prop, $val, $isBool = false, $boolVerb = 'is')
    {
        $entity = $this->getEntity();
        $refObject = new ReflectionObject($entity);
        $refProperty = $refObject->getProperty($prop);
        $refProperty->setAccessible(true);
        $refProperty->setValue($entity, $val);
        $this->assertEquals($val, $entity->{(!$isBool ? 'get' : $boolVerb) . ucfirst($prop)}());
    }

    /**
     * @dataProvider setterTestDataProvider
     */
    public function testSetter($prop, $val, $isBool = false, $boolVerb = 'is')
    {
        $entity = $this->getEntity();
        $this->assertEquals($entity, $entity->{'set' . ucfirst($prop)}($val));
        $this->assertEquals($val, $entity->{(!$isBool ? 'get' : $boolVerb) . ucfirst($prop)}());
    }

    protected function getAlphaNumeric($withSymbols = false, $length = 0) {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . ($withSymbols? '0123456789!@#$%^&*()_-' : ''));
        $invalidSeed = str_split('!@#$%^&*()_-');
        $name = '';
        foreach (array_rand($seed, mt_rand(5, count($seed))) as $k)
        {
            $name .= $seed[$k];
        }
        if ($length) {
            $length = min($length, strlen($name));
            $name = substr($name, 0, $withSymbols ? ($length - 1) : $length);
        }
        if ($withSymbols) {
            // Make sure we have at least one invalid character.
            $name .= $invalidSeed[array_rand($invalidSeed)];
        }
        return $name;
    }

    abstract public function getterTestDataProvider();

    abstract public function setterTestDataProvider();

    abstract protected function getEntity();

}

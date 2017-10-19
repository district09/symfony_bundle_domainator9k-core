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
    public function testGetter($prop, $val, $isBool = false)
    {
        $entity = $this->getEntity();
        $refObject = new ReflectionObject($entity);
        $refProperty = $refObject->getProperty($prop);
        $refProperty->setAccessible(true);
        $refProperty->setValue($entity, $val);
        $this->assertEquals($val, $entity->{(!$isBool ? 'get' : 'is') . ucfirst($prop)}());
    }

    /**
     * @dataProvider setterTestDataProvider
     */
    public function testSetter($prop, $val, $isBool = false)
    {
        $entity = $this->getEntity();
        $this->assertEquals($entity, $entity->{'set' . ucfirst($prop)}($val));
        $this->assertEquals($val, $entity->{(!$isBool ? 'get' : 'is') . ucfirst($prop)}());
    }

    abstract public function getterTestDataProvider();

    abstract public function setterTestDataProvider();

    abstract protected function getEntity();

}

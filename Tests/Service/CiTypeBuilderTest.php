<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\CiTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CiTypeBuilderTest.
 *
 * @author Jelle Sebreghts
 */
class CiTypeBuilderTest extends TestCase
{
    use DataGenerator;

    public function testAddGetType()
    {
        $service = $this->getService();
        $type = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $slug = $this->getAlphaNumeric();
        $type->expects($this->any())->method('getSlug')->willReturn($slug);
        $this->assertEquals($type, $service->addType($type)->getType($slug));
        $this->assertEquals($type, $service->getTypes()[$slug]);
    }

    public function testAddGetTypeSlugs()
    {
        $service = $this->getService();
        $type = $this->getMockBuilder(CiTypeInterface::class)->disableOriginalConstructor()->getMock();
        $slug = $this->getAlphaNumeric();
        $type->expects($this->any())->method('getSlug')->willReturn($slug);
        $this->assertEquals([$slug => $slug], $service->addType($type)->getTypeSlugs());
    }

    protected function getService()
    {
        return new CiTypeBuilder();
    }
}

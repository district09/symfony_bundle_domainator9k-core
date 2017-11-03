<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of ApplicationTypeBuilderTest
 *
 * @author Jelle Sebreghts
 */
class ApplicationTypeBuilderTest extends TestCase
{

    use DataGenerator;

    public function testAddGetType()
    {
        $service = $this->getService();
        $type = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();
        $slug = $this->getAlphaNumeric();
        $type->expects($this->any())->method('getSlug')->willReturn($slug);
        $this->assertEquals($type, $service->addType($type)->getType($slug));
        $this->assertEquals($type, $service->getTypes()[$slug]);
    }

    protected function getService()
    {
        return new ApplicationTypeBuilder();
    }

}

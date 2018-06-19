<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Provider;

use DigipolisGent\Domainator9k\CoreBundle\CacheClearer\CacheClearerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CacheClearProvider;
use PHPUnit\Framework\TestCase;

class CacheClearProviderTest extends TestCase
{

    public function testRegister()
    {
        $cacheClearProvider = new CacheClearProvider();
        $object = $this->getMockBuilder('\stdClass')->getMock();
        $class = get_class($object);
        $clearer = $this->getMockBuilder(CacheClearerInterface::class)->getMock();
        $cacheClearProvider->registerCacheClearer($clearer, $class);

        $this->assertEquals($clearer, $cacheClearProvider->getCacheClearerFor($object));
    }

    /**
     * @expectedException \DigipolisGent\Domainator9k\CoreBundle\Exception\NoCacheClearerFoundException
     */
    public function testNoClearer()
    {
        $cacheClearProvider = new CacheClearProvider();
        $object = $this->getMockBuilder('\stdClass')->getMock();
        $class = get_class($object);
        $cacheClearProvider->getCacheClearerFor($object);
    }

}

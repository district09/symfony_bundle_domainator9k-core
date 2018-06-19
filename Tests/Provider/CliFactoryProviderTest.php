<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Provider;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Provider\CliFactoryProvider;
use PHPUnit\Framework\TestCase;

class CliFactoryProviderTest extends TestCase
{

    public function testRegister()
    {
        $cliFactoryProvider = new CliFactoryProvider();
        $object = $this->getMockBuilder('\stdClass')->getMock();
        $class = get_class($object);
        $cli = $this->getMockBuilder(CliInterface::class)->getMock();
        $factory = $this->getMockBuilder(CliFactoryInterface::class)->getMock();
        $factory->expects($this->once())->method('create')->with($object)->willReturn($cli);
        $cliFactoryProvider->registerCliFactory($factory, $class);

        $this->assertEquals($cli, $cliFactoryProvider->createCliFor($object));
    }

    /**
     * @expectedException \DigipolisGent\Domainator9k\CoreBundle\Exception\NoCliFactoryFoundException
     */
    public function testNoClearer()
    {
        $cacheClearProvider = new CliFactoryProvider();
        $object = $this->getMockBuilder('\stdClass')->getMock();
        $class = get_class($object);
        $cacheClearProvider->createCliFor($object);
    }

    public function testDefaultFactory()
    {
        $cli = $this->getMockBuilder(CliInterface::class)->getMock();
        $object = $this->getMockBuilder('\stdClass')->getMock();
        $factory = $this->getMockBuilder(CliFactoryInterface::class)->getMock();
        $factory->expects($this->once())->method('create')->with($object)->willReturn($cli);
        $cliFactoryProvider = new CliFactoryProvider($factory);

        $this->assertEquals($cli, $cliFactoryProvider->createCliFor($object));
    }

}

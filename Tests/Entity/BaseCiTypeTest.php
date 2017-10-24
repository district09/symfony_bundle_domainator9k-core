<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeSettingsInterface;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType;
use LogicException;
use ReflectionObject;

/**
 * Description of BaseCiTypeTest
 *
 * @author Jelle Sebreghts
 */
class BaseCiTypeTest extends EntityTest
{

    public function testBuildCiUrl()
    {
        $ciType = $this->getEntity();
        $settings = $this->getMockBuilder(CiTypeSettingsInterface::class)->getMock();
        $url = 'http://' . $this->getAlphaNumeric() . '.com';
        $settings->expects($this->any())->method('getUrl')->will($this->onConsecutiveCalls($url, $url . '/'));
        $env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $jobName = $this->getAlphaNumeric();
        $settings->expects($this->any())->method('getJobName')->with($env)->willReturn($jobName);
        $this->assertEquals($url . '/job/' . $jobName, $ciType->buildCiUrl($settings, $env));
        $this->assertEquals($url . '/job/' . $jobName, $ciType->buildCiUrl($settings, $env));
    }

    public function testBuildUrl()
    {
        $ciType = $this->getEntity();
        $settings = $this->getMockBuilder(CiTypeSettingsInterface::class)->getMock();
        $url = 'http://' . $this->getAlphaNumeric() . '.com';
        $settings->expects($this->any())->method('getUrl')->willReturn($url);
        $this->assertEquals($settings->getUrl(), $ciType->buildUrl($settings));
    }

    public function testParseYamlConfig()
    {
        $type = $this->getEntity();
        $type->parseYamlConfig();
        $this->assertEquals('config123', $type->getAdditionalConfig());
        $this->assertEquals('Stub', $type->getName());
        $this->assertEquals('stub', $type->getSlug());

        $refObject = new ReflectionObject($type);
        $refProperty = $refObject->getProperty('ymlConfigName');
        $refProperty->setAccessible(true);
        $refProperty->setValue($type, 'custom_stub_config.yml');

        $type->parseYamlConfig();
        $this->assertEquals('custom config123', $type->getAdditionalConfig());
        $this->assertEquals('Custom Stub', $type->getName());
        $this->assertEquals('custom_stub', $type->getSlug());
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage SettingsFormClass in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetSettingsFormClass()
    {
        $type = $this->getEntity();
        $type->getSettingsFormClass();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage SettingsEntityClass in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetSettingsEntityClass()
    {
        $type = $this->getEntity();
        $type->getSettingsEntityClass();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage ProcessorServiceClass in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetProcessorServiceClass()
    {
        $type = $this->getEntity();
        $type->getProcessorServiceClass();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage AppTypeSettingsFormClass in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetAppTypeSettingsFormClass()
    {
        $type = $this->getEntity();
        $type->getAppTypeSettingsFormClass();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage AppTypeSettingsEntityClass in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetAppTypeSettingsEntityClass()
    {
        $type = $this->getEntity();
        $type->getAppTypeSettingsEntityClass();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Name in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetName()
    {
        $type = $this->getEntity();
        $type->getName();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Slug in CiType DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\CiType\TestCiType cannot be false
     */
    public function testGetSlug()
    {
        $type = $this->getEntity();
        $type->getSlug();
    }

    public function getterTestDataProvider()
    {
        return [
            ['menuUrlFieldName', $this->getAlphaNumeric()],
            ['settingsFormClass', $this->getAlphaNumeric()],
            ['settingsEntityClass', $this->getAlphaNumeric()],
            ['processorServiceClass', $this->getAlphaNumeric()],
            ['appTypeSettingsFormClass', $this->getAlphaNumeric()],
            ['appTypeSettingsEntityClass', $this->getAlphaNumeric()],
            ['name', $this->getAlphaNumeric()],
            ['slug', $this->getAlphaNumeric()],
            ['additionalConfig', $this->getAlphaNumeric()],
            ['ymlConfigName', $this->getAlphaNumeric()],
        ];
    }

    /**
     * @dataProvider setterTestDataProvider
     */
    public function testSetter($prop, $val, $isBool = false, $boolVerb = 'is')
    {
        $this->assertTrue(true);
    }

    public function setterTestDataProvider()
    {
        return [['a', 'b']];
    }

    /**
     *
     * @return TestCiType
     */
    protected function getEntity()
    {
        return new TestCiType();
    }

}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironmentSettings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

/**
 * Description of SettingsTest
 *
 * @author Jelle Sebreghts
 */
class SettingsTest extends EntityTest
{

    public function testConstructor()
    {
        $settings = $this->getEntity();
        $this->assertInstanceOf(ArrayCollection::class, $settings->getDefaultSshKeyGroups());
    }

    public function testSetDefaultSshKeyGroup()
    {
        $settings = $this->getEntity();
        $group1 = $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock();
        $group2 = $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock();
        $group = [
            $group1,
            $group2,
        ];
        $settings->setDefaultSshKeyGroups($group);
        $this->assertInstanceOf(ArrayCollection::class, $settings->getDefaultSshKeyGroups());
        $this->assertEquals($group1, $settings->getDefaultSshKeyGroups()[0]);
        $this->assertEquals($group2, $settings->getDefaultSshKeyGroups()[1]);
    }

    public function testGetAppEnvironmentSettingsValidString()
    {
        $settings = $this->getEntity();
        $envSettings1 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name1 = $this->getAlphaNumeric();
        $envSettings1->expects($this->once())->method('getEnvironment')->willReturn($name1);
        $envSettings2 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name2 = $this->getAlphaNumeric();
        $envSettings2->expects($this->once())->method('getEnvironment')->willReturn($name2);
        $settings->setAppEnvironmentSettings([$envSettings1, $envSettings2]);
        $this->assertEquals($envSettings2, $settings->getAppEnvironmentSettings($name2));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetAppEnvironmentSettingsInValidString()
    {
        $settings = $this->getEntity();
        $envSettings1 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name1 = $this->getAlphaNumeric();
        $envSettings1->expects($this->once())->method('getEnvironment')->willReturn($name1);
        $envSettings2 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name2 = $this->getAlphaNumeric();
        $envSettings2->expects($this->once())->method('getEnvironment')->willReturn($name2);
        $settings->setAppEnvironmentSettings([$envSettings1, $envSettings2]);
        $settings->getAppEnvironmentSettings($this->getAlphaNumeric());
    }

    public function testGetAppEnvironmentSettingsValidAppEnv()
    {
        $settings = $this->getEntity();
        $envSettings1 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name1 = $this->getAlphaNumeric();
        $envSettings1->expects($this->once())->method('getEnvironment')->willReturn($name1);
        $envSettings2 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name2 = $this->getAlphaNumeric();
        $envSettings2->expects($this->once())->method('getEnvironment')->willReturn($name2);
        $settings->setAppEnvironmentSettings([$envSettings1, $envSettings2]);
        $env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $env->expects($this->once())->method('getNameCanonical')->willReturn($name2);
        $this->assertEquals($envSettings2, $settings->getAppEnvironmentSettings($env));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetAppEnvironmentSettingsInValidAppEnv()
    {
        $settings = $this->getEntity();
        $envSettings1 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name1 = $this->getAlphaNumeric();
        $envSettings1->expects($this->once())->method('getEnvironment')->willReturn($name1);
        $envSettings2 = $this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock();
        $name2 = $this->getAlphaNumeric();
        $envSettings2->expects($this->once())->method('getEnvironment')->willReturn($name2);
        $settings->setAppEnvironmentSettings([$envSettings1, $envSettings2]);
        $env = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $env->expects($this->once())->method('getNameCanonical')->willReturn($this->getAlphaNumeric());
        $settings->getAppEnvironmentSettings($env);
    }

    public function testAddDeleteDefaultSshKeyGroup()
    {
        $settings = $this->getEntity();
        $group = $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals($settings, $settings->addDefaultSshKeyGroup($group));
        $this->assertEquals($group, $settings->getDefaultSshKeyGroups()[0]);
        $this->assertEquals($settings, $settings->removeDefaultSshKeyGroup($group));
        $this->assertEmpty($settings->getDefaultSshKeyGroups());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['defaultSockSshKeys', $this->getAlphaNumeric()],
            ['dnsMailRecipients', $this->getAlphaNumeric()],
            ['dnsMailTemplate', $this->getAlphaNumeric()],
            ['sockDomain', $this->getAlphaNumeric()],
            ['sockUserToken', $this->getAlphaNumeric()],
            ['sockClientToken', $this->getAlphaNumeric()],
            ['appEnvironmentSettings', [$this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock()]],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['defaultSockSshKeys', $this->getAlphaNumeric()],
            [
                'defaultSshKeyGroups',
                new ArrayCollection(
                    [
                    $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock(),
                    $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock(),
                    ]
                ),
            ],
            ['dnsMailRecipients', $this->getAlphaNumeric()],
            ['dnsMailTemplate', $this->getAlphaNumeric()],
            ['sockDomain', $this->getAlphaNumeric()],
            ['sockUserToken', $this->getAlphaNumeric()],
            ['sockClientToken', $this->getAlphaNumeric()],
            ['appEnvironmentSettings', [$this->getMockBuilder(AppEnvironmentSettings::class)->disableOriginalConstructor()->getMock()]],
        ];
    }

    /**
     *
     * @return Settings
     */
    protected function getEntity()
    {
        return new Settings();
    }

}

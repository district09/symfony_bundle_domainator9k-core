<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\SshKey;
use DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup;

/**
 * Description of SshKeyTest
 *
 * @author Jelle Sebreghts
 */
class SshKeyTest extends EntityTest
{

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $key;

    protected function setUp()
    {
        parent::setUp();
        $this->name = $this->getAlphaNumeric();
        $this->key = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $key = $this->getEntity();
        $this->assertEquals($this->name, $key->getName());
        $this->assertEquals($this->key, $key->getContent());
    }

    public function testGetGroupsAsString()
    {
        $key = $this->getEntity();

        $group1 = $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock();
        $label1 = $this->getAlphaNumeric();
        $group1->expects($this->any())->method('getLabel')->willReturn($label1);

        $group2 = $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock();
        $label2 = $this->getAlphaNumeric();
        $group2->expects($this->any())->method('getLabel')->willReturn($label2);

        $group3 = $this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock();
        $label3 = $this->getAlphaNumeric();
        $group3->expects($this->any())->method('getLabel')->willReturn($label3);

        $groups = [$group1, $group2, $group3];
        $labels = [$label1, $label2, $label3];

        sort($labels);

        $key->setGroups($groups);

        $this->assertEquals(implode(', ', $labels), $key->getGroupsAsString());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['name', $this->getAlphaNumeric()],
            ['content', $this->getAlphaNumeric()],
            ['groups', [$this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock()]],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['name', $this->getAlphaNumeric()],
            ['content', $this->getAlphaNumeric()],
            ['groups', [$this->getMockBuilder(SshKeyGroup::class)->disableOriginalConstructor()->getMock()]],
        ];
    }

    /**
     *
     * @return SshKey
     */
    protected function getEntity()
    {
        return new SshKey($this->name, $this->key);
    }

}

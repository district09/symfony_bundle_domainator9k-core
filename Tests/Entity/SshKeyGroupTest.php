<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\SshKey;
use DigipolisGent\Domainator9k\CoreBundle\Entity\SshKeyGroup;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\EntityTest;

/**
 * Description of SshKeyGroupTest
 *
 * @author Jelle Sebreghts
 */
class SshKeyGroupTest extends EntityTest
{

    protected $label;

    protected function setUp()
    {
        parent::setUp();
        $this->label = $this->getAlphaNumeric();
    }

    public function testConstructor()
    {
        $keyGroup = $this->getEntity();
        $this->assertEquals($this->label, $keyGroup->getLabel());
    }

    public function getterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['label', $this->getAlphaNumeric()],
            ['keys', $this->getMockBuilder(SshKey::class)->disableOriginalConstructor()->getMock()],
        ];
    }

    public function setterTestDataProvider()
    {
        return [
            ['id', uniqid()],
            ['label', $this->getAlphaNumeric()],
            ['keys', $this->getMockBuilder(SshKey::class)->disableOriginalConstructor()->getMock()],
        ];
    }

    /**
     *
     * @return SshKeyGroup
     */
    protected function getEntity()
    {
        return new SshKeyGroup($this->label);
    }

}

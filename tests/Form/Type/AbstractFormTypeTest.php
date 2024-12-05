<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\SettingBundle\Service\FormService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFormTypeTest extends TestCase
{

    /**
     * @return MockObject
     */
    protected function getFormBuilderMock()
    {
        $mock = $this
            ->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return MockObject
     */
    protected function getOptionsResolverMock()
    {
        $mock = $this
            ->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return MockObject
     */
    protected function getFormServiceMock()
    {
        $mock = $this
            ->getMockBuilder(FormService::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}

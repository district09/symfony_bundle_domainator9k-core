<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\EnvironmentFormType;

class EnvironmentFormTypeTest extends AbstractFormTypeTest
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->atLeastOnce())
            ->method('setDefault')
            ->with('data_class', Environment::class);

        $formType = new EnvironmentFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            ['name'],
            ['prod'],
        ];

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('add')
            ->withConsecutive(...$arguments);

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new EnvironmentFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

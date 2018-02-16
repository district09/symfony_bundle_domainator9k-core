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
            ->expects($this->at(0))
            ->method('setDefault')
            ->with('data_class', Environment::class);

        $formType = new EnvironmentFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            'name',
            'prod',
        ];

        $index = 0;

        foreach ($arguments as $argument) {
            $formBuilder
                ->expects($this->at($index))
                ->method('add')
                ->with($argument);

            $index++;
        }

        $formBuilder
            ->expects($this->at($index))
            ->method('addEventSubscriber');

        $formType = new EnvironmentFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

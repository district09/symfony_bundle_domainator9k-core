<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\ApplicationEnvironmentFormType;

class ApplicationEnvironmentFormTypeTest extends AbstractFormTypeTest
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->at(0))
            ->method('setDefault')
            ->with('data_class', ApplicationEnvironment::class);

        $formType = new ApplicationEnvironmentFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            'domain',
            'databaseName',
            'databaseUser',
            'databasePassword',
            'gitRef',
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

        $formType = new ApplicationEnvironmentFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

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
            ->expects($this->atLeastOnce())
            ->method('setDefault')
            ->with('data_class', ApplicationEnvironment::class);

        $formType = new ApplicationEnvironmentFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            ['domain'],
            ['databaseName'],
            ['databaseUser'],
            ['databasePassword'],
            ['gitRef'],
        ];

        $formBuilder
            ->expects($this->atLeast(count($arguments)))
            ->method('add')
            ->withConsecutive(...$arguments);

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new ApplicationEnvironmentFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Form\Type\ApplicationFormType;

class AbstractApplicationFormTypeTest extends AbstractFormTypeTest
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->atLeastOnce())
            ->method('setDefault')
            ->with('data_class', AbstractApplication::class);

        $optionsResolver
            ->expects($this->atLeastOnce())
            ->method('setRequired')
            ->with('form_service');

        $formType = new ApplicationFormType();
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            ['name'],
            ['gitRepo'],
            ['hasDatabase'],
        ];

        $formBuilder->expects($this->atLeastOnce())
            ->method('add')
            ->withConsecutive(...$arguments);

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new ApplicationFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, ['form_service' => $this->getFormServiceMock()]);
    }
}

<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\ApplicationTypeEnvironmentFormType;

class ApplicationTypeEnvironmentFormTypeTest extends AbstractFormType
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->atLeastOnce())
            ->method('setDefault')
            ->with('data_class', ApplicationTypeEnvironment::class);

        $formType = new ApplicationTypeEnvironmentFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new ApplicationTypeEnvironmentFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

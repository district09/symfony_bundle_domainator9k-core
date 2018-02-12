<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\ApplicationTypeEnvironmentFormType;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\ServerFormType;

class ApplicationTypeEnvironmentFormTypeTest extends AbstractFormTypeTest
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->at(0))
            ->method('setDefault')
            ->with('data_class', ApplicationTypeEnvironment::class);

        $formType = new ApplicationTypeEnvironmentFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $index = 0;

        $formBuilder
            ->expects($this->at($index))
            ->method('addEventSubscriber');

        $formType = new ApplicationTypeEnvironmentFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

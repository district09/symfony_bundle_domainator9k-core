<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\VirtualServerFormType;

class VirtualServerFormTypeTest extends AbstractFormType
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->atLeastOnce())
            ->method('setDefault')
            ->with('data_class', VirtualServer::class);

        $formType = new VirtualServerFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            ['name'],
            ['host'],
            ['port'],
            ['environment'],
            ['taskServer'],
        ];

        $formBuilder
            ->expects($this->atLeast(count($arguments)))
            ->method('add')
            ->withConsecutive(...$arguments);

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new VirtualServerFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

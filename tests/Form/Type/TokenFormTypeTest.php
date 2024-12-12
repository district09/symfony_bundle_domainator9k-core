<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\TokenFormType;

class TokenFormTypeTest extends AbstractFormType
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->atLeastOnce())
            ->method('setDefault')
            ->with('data_class', Token::class);

        $formType = new TokenFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            ['name'],
            ['value'],
        ];

        $formBuilder
            ->expects($this->atLeast(count($arguments)))
            ->method('add')
            ->withConsecutive(...$arguments);

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new TokenFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

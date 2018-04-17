<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\TokenFormType;

class TokenFormTypeTest extends AbstractFormTypeTest
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->at(0))
            ->method('setDefault')
            ->with('data_class', Token::class);

        $formType = new TokenFormType($this->getFormServiceMock());
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $arguments = [
            'name',
            'value',
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

        $formType = new TokenFormType($this->getFormServiceMock());
        $formType->buildForm($formBuilder, []);
    }
}

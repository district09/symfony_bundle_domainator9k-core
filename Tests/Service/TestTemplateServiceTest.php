<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;


use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Bar;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Foo;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Qux;
use PHPUnit\Framework\TestCase;

class TemplateServiceTest extends TestCase
{

    /**
     * @expectedException \DigipolisGent\Domainator9k\CoreBundle\Exception\TemplateException
     */
    public function testReplaceKeysWithInvalidEntity()
    {
        $templateService = new TemplateService();

        $text = <<<EOL
        This is a random text.
EOL;

        $entities = [
            'bar' => new Bar(),
        ];

        $templateService->replaceKeys($text, $entities);
    }

    public function testReplaceKeysWithValidEntity()
    {
        $templateService = new TemplateService();

        $text = <<<EOL
        Primary title : [[ foo:primary() ]].
        Second title : [[ foo:second() ]].
        Result : [[ foo:multiply(3,4) ]].
EOL;

        $foo = new Foo();
        $foo->setPrimaryTitle('Pieter');
        $foo->setSecondTitle('Massoels');

        $entities = [
            'foo' => $foo,
        ];

        $actual = $templateService->replaceKeys($text, $entities);

        $expected = <<<EOL
        Primary title : Pieter.
        Second title : Massoels.
        Result : 12.
EOL;

        $this->assertEquals($expected, $actual);
    }

    public function testReplaceKeysRecursively()
    {
        $templateService = new TemplateService();

        $text = <<<EOL
        Qux title : [[ foo:quxTitle() ]].
        Qux subtitle : [[ foo:quxSubtitle() ]].
EOL;

        $qux = new Qux();
        $qux->setTitle('Qux title example');
        $qux->setSubTitle('[[ foo:primary() ]]');

        $foo = new Foo();
        $foo->setPrimaryTitle('Pieter');
        $foo->setSecondTitle('Massoels');
        $foo->setQux($qux);

        $entities = [
            'foo' => $foo,
        ];

        $actual = $templateService->replaceKeys($text, $entities);

        $expected = <<<EOL
        Qux title : Qux title example.
        Qux subtitle : Pieter.
EOL;

        $this->assertEquals($expected, $actual);
    }
}

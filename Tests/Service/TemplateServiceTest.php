<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;


use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Bar;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Foo;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Qux;
use PHPUnit\Framework\TestCase;

class TemplateServiceTest extends TestCase
{

    protected $token;
    protected $tokenService;

    protected function setUp()
    {
        parent::setUp();
        $name = uniqid();
        $value = uniqid();
        $token = new Token();
        $token->setName($name);
        $token->setValue($value);
        $this->token = $token;
        $this->tokenService = $this->getTokenServiceMock();
    }

    /**
     * @expectedException \DigipolisGent\Domainator9k\CoreBundle\Exception\TemplateException
     */
    public function testReplaceKeysWithInvalidEntity()
    {
        $templateService = new TemplateService($this->tokenService);

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
        $templateService = new TemplateService($this->tokenService);
        $name = ucfirst($this->token->getName());
        $value = $this->token->getValue();
        $text = <<<EOL
        Primary title : [[ foo:primary() ]].
        Second title : [[ foo:second() ]].
        Result : [[ foo:multiply(3,4) ]].
        Custom token : [[ token:get{$name}]].
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
        Custom token : {$value}.
EOL;

        $this->assertEquals($expected, $actual);
    }

    public function testReplaceKeysRecursively()
    {
        $templateService = new TemplateService($this->tokenService);
        $name = ucfirst($this->token->getName());
        $value = $this->token->getValue();

        $text = <<<EOL
        Qux title : [[ foo:quxTitle() ]].
        Qux subtitle : [[ foo:quxSubtitle() ]].
EOL;

        $qux = new Qux();
        $qux->setTitle("[[ token:get{$name}]]");
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
        Qux title : {$value}.
        Qux subtitle : Pieter.
EOL;

        $this->assertEquals($expected, $actual);
    }

    protected function getTokenServiceMock()
    {
        $this->repository->expects($this->any())->method('findAll')->willReturn([$this->token]);
        $this->repository->expects($this->any())->method('findOneBy')->with(['name' => $this->token->getName()])->willReturn($this->token);
    }
}

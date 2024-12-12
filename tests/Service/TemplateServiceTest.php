<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TokenService;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Bar;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Foo;
use DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity\Qux;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class TemplateServiceTest extends TestCase
{

    protected $token;
    protected $tokenService;
    protected $repository;
    protected EntityManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $token = new Token();
        $token->setName(substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 10));
        $token->setValue(substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 10));
        $this->token = $token;
        $this->repository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository->expects($this->any())->method('findAll')->willReturn([$token]);
        $this->repository->expects($this->any())->method('findOneBy')->with(['name' => $token->getName()])->willReturn($token);
        $this->entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Token::class)
            ->willReturn($this->repository);
        $this->tokenService = new TokenService($this->entityManager);
    }

    public function testReplaceKeysWithInvalidEntity()
    {
        $this->expectException(\DigipolisGent\Domainator9k\CoreBundle\Exception\TemplateException::class);
        $templateService = new TemplateService([], $this->tokenService);

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
        $templateService = new TemplateService([], $this->tokenService);
        $name = $this->token->getName();
        $value = $this->token->getValue();
        $text = <<<EOL
        Primary title : [[ foo:primary() ]].
        Second title : [[ foo:second() ]].
        Result : [[ foo:multiply(3,4) ]].
        Custom token : [[ token:{$name}() ]].
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
        $templateService = new TemplateService([], $this->tokenService);
        $name = $this->token->getName();
        $value = $this->token->getValue();

        $text = <<<EOL
        Qux title : [[ foo:quxTitle() ]].
        Qux subtitle : [[ foo:quxSubtitle() ]].
EOL;

        $qux = new Qux();
        $qux->setTitle("[[ token:{$name}() ]]");
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
}

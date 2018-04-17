<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use DigipolisGent\Domainator9k\CoreBundle\Service\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{

    protected $tokenService;
    protected $entityManager;
    protected $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Token::class)
            ->willReturn($this->repository);
        $this->tokenService = new TokenService($this->entityManager);
    }

    protected function testGetTemplateReplacements()
    {
        $name = uniqid();
        $value = uniqid();
        $token = new Token();
        $token->setName($name);
        $token->setValue($value);
        $this->repository->expects($this->once())->method('findAll')->willReturn([$token]);
        $this->assertEquals([$name . '()' => 'get' . ucfirst($name) . '()'], $this->tokenService->getTemplateReplacements());
    }

    protected function testMagicCallMethod()
    {
        $name = uniqid();
        $value = uniqid();
        $token = new Token();
        $token->setName($name);
        $token->setValue($value);
        $this->repository->expects($this->once())->method('findAll')->willReturn([$token]);
        $this->repository->expects($this->once())->method('findOneBy')->with(['name' => $name])->willReturn($token);
        $method = 'get' . ucfirst($name);
        $this->assertEquals($value, $this->tokenService->{$method}());
    }
}

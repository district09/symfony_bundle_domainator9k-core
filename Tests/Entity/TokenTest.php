<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{

    public function testGettersAndSetters()
    {
        $name = uniqid();
        $value= uniqid();
        $token = new Token();
        $this->assertSame($token, $token->setName($name));
        $this->assertSame($token, $token->setValue($value));
        $this->assertEquals($token->getName(), $name);
        $this->assertEquals($token->getValue(), $value);
    }

}

<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\TemplateImplementationTrait;

class Foo implements TemplateInterface
{

    use IdentifiableTrait;
    use TemplateImplementationTrait;

    private $primaryTitle;

    private $secondTitle;

    private $qux;

    public static function additionalTemplateReplacements(): array
    {
        return [
            'primary()' => 'getPrimaryTitle()',
            'second()' => 'getSecondTitle()',
            'multiply(a,b)' => 'multiplyNumbers(a,b)',
        ];
    }

    /**
     * @return mixed
     */
    public function getPrimaryTitle(): string
    {
        return $this->primaryTitle;
    }

    /**
     * @param mixed $primaryTitle
     */
    public function setPrimaryTitle(string $primaryTitle)
    {
        $this->primaryTitle = $primaryTitle;
    }

    /**
     * @return mixed
     */
    public function getSecondTitle(): string
    {
        return $this->secondTitle;
    }

    /**
     * @param mixed $secondTitle
     */
    public function setSecondTitle(string $secondTitle)
    {
        $this->secondTitle = $secondTitle;
    }

    /**
     * @return mixed
     */
    public function getQux(): Qux
    {
        return $this->qux;
    }

    /**
     * @param mixed $qux
     */
    public function setQux(Qux $qux)
    {
        $this->qux = $qux;
    }

    public function multiplyNumbers(int $a, int $b): int
    {
        return $a * $b;
    }
}

<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;

class Foo implements TemplateInterface
{

    use IdentifiableTrait;

    private $primaryTitle;

    private $secondTitle;

    private $qux;

    public static function getTemplateReplacements(): array
    {
        return [
            'primary()' => 'getPrimaryTitle()',
            'second()' => 'getSecondTitle()',
            'quxTitle()' => 'getQux().getTitle()',
            'quxSubtitle()' => 'getQux().getSubtitle()',
            'multiply(a,b)' => 'multiplyNumbers(a,b)',
        ];
    }

    /**
     * @return mixed
     */
    public function getPrimaryTitle()
    {
        return $this->primaryTitle;
    }

    /**
     * @param mixed $primaryTitle
     */
    public function setPrimaryTitle($primaryTitle)
    {
        $this->primaryTitle = $primaryTitle;
    }

    /**
     * @return mixed
     */
    public function getSecondTitle()
    {
        return $this->secondTitle;
    }

    /**
     * @param mixed $secondTitle
     */
    public function setSecondTitle($secondTitle)
    {
        $this->secondTitle = $secondTitle;
    }

    /**
     * @return mixed
     */
    public function getQux()
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

    public function multiplyNumbers($a, $b)
    {
        return $a * $b;
    }
}

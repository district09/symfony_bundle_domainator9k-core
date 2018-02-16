<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Fixtures\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;

class Qux implements TemplateInterface
{

    use IdentifiableTrait;

    private $title;

    private $subtitle;

    public static function getTemplateReplacements(): array
    {
        return [
            'title()' => 'getTitle()',
            'subtitle()' => 'getSub()',
        ];
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }
}

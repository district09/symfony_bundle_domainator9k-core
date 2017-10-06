<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseCiType;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\CiTypeInterface;

class CiTypeBuilder
{
    private $ciTypes;

    public function __construct()
    {
        $this->ciTypes = [];
    }

    /**
     * @return array|BaseCiType[]
     */
    public function getTypes()
    {
        $arr = [];
        foreach ($this->ciTypes as $type) {
            $arr[$type->getSlug()] = $type;
        }

        return $arr;
    }

    public function getTypeSlugs()
    {
        $arr = [];
        foreach ($this->ciTypes as $type) {
            $slug = $type->getSlug();
            $arr[$slug] = $slug;
        }

        return $arr;
    }

    /**
     * @param $slug
     *
     * @return CiTypeInterface
     */
    public function getType($slug)
    {
        return $this->ciTypes[$slug];
    }

    public function addType(CiTypeInterface $ciType)
    {
        $this->ciTypes[$ciType->getSlug()] = $ciType;
    }
}

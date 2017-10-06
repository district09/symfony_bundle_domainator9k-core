<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;

class ApplicationTypeBuilder
{
    private $applicationTypes;

    public function __construct()
    {
        $this->applicationTypes = [];
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $arr = [];
        foreach ($this->applicationTypes as $type) {
            $arr[$type->getSlug()] = $type;
        }

        return $arr;
    }

    /**
     * @param $slug
     *
     * @return BaseAppType|ApplicationType
     *
     * @throws \Exception
     */
    public function getType($slug)
    {
        return $this->applicationTypes[$slug];
    }

    //todo interface
    public function addType(BaseAppType $applicationType)
    {
        $this->applicationTypes[$applicationType->getSlug()] = $applicationType;
    }
}

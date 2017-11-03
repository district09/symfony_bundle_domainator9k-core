<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Interfaces\ApplicationTypeInterface;
use Exception;

class ApplicationTypeBuilder
{

    protected $applicationTypes;

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
        foreach ($this->applicationTypes as $type)
        {
            $arr[$type->getSlug()] = $type;
        }

        return $arr;
    }

    /**
     * @param $slug
     *
     * @return BaseAppType
     *
     * @throws Exception
     */
    public function getType($slug)
    {
        return $this->applicationTypes[$slug];
    }

    public function addType(ApplicationTypeInterface $applicationType)
    {
        $this->applicationTypes[$applicationType->getSlug()] = $applicationType;

        return $this;
    }

}

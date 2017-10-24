<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\AppType;

use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;

/**
 * Description of TestAppType
 *
 * @author Jelle Sebreghts
 */
class TestAppType extends BaseAppType
{
    public function getConfigFiles(\DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment $env, array $servers, \DigipolisGent\Domainator9k\CoreBundle\Entity\Settings $settings)
    {
        return '';
    }

}

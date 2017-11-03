<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity\Stub\AppType;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;

/**
 * Description of TestAppType
 *
 * @author Jelle Sebreghts
 */
class TestAppType extends BaseAppType
{
    public function getConfigFiles(AppEnvironment $env, array $servers, Settings $settings)
    {
        return '';
    }

}

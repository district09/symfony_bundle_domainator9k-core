<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\BaseAppType;
use DigipolisGent\Domainator9k\CoreBundle\EventListener\AppTypeInjectorListener;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of AppTypeInjectorListenerTest.
 *
 * @author Jelle Sebreghts
 */
class AppTypeInjectorListenerTest extends TestCase
{
    use DataGenerator;

    public function testPostLoad()
    {
        $slug = $this->getAlphaNumeric();

        $appType = $this->getMockBuilder(BaseAppType::class)->disableOriginalConstructor()->getMock();

        $appTypeBuilder = $this->getMockBuilder(ApplicationTypeBuilder::class)->disableOriginalConstructor()->getMock();
        $appTypeBuilder->expects($this->once())->method('getType')->with($slug)->willReturn($appType);

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('setType')->with($appType);
        $app->expects($this->once())->method('getAppTypeSlug')->willReturn($slug);

        $listener = new AppTypeInjectorListener($appTypeBuilder);
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $args->expects($this->once())->method('getEntity')->willReturn($app);
        $listener->postLoad($args);
    }
}

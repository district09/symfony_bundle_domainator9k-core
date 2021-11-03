<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\CLI;

use DigipolisGent\Domainator9k\CoreBundle\CLI\DefaultCliFactory;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;

class DefaultCliFactoryTest extends TestCase
{
    protected $keyCreated = false;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a bogus ssh key if one doens't exist, so the tests won't throw
        // warnings.
        $key = rtrim(Path::getHomeDirectory(), '/') . '/.ssh/id_rsa';
        if (!file_exists($key)) {
          file_put_contents($key, 'bogus ssh key');
          $this->keyCreated = true;
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->keyCreated) {
            unlink(rtrim(Path::getHomeDirectory(), '/') . '/.ssh/id_rsa');
        }
    }

    public function testCreate()
    {
        // We have untestable code in DefaultCliFactory since the ssh client
        // can't me mocked and it would be serious overkill to abstract it to
        // yet another factory just so we can mock it here.
        $factory = new DefaultCliFactory();
        $appEnv = $this->getMockBuilder(ApplicationEnvironment::class)->getMock();
        $env = $this->getMockBuilder(Environment::class)->getMock();
        $app = $this->getMockBuilder(AbstractApplication::class)->getMock();
        $appEnv->expects($this->once())->method('getEnvironment')->willReturn($env);
        $appEnv->expects($this->once())->method('getApplication')->willReturn($app);

        $server = $this->getMockBuilder(VirtualServer::class)->getMock();
        $server->expects($this->once())->method('isTaskServer')->willreturn(false);

        $env->expects($this->once())->method('getVirtualServers')->willReturn(new ArrayCollection([$server]));
        $this->assertNull($factory->create($appEnv));
    }

    public function testCreateUnsuppoerted()
    {
        $this->expectException(\InvalidArgumentException::class);
        $factory = new DefaultCliFactory();
        $object = $this->getMockBuilder('\stdClass')->getMock();
        $factory->create($object);
    }

}

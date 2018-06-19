<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Webmozart\PathUtil\Path;

class DefaultCliFactory implements CliFactoryInterface
{
    public function create($object): ?CliInterface
    {
        if (!($object instanceof ApplicationEnvironment)) {
            throw new \InvalidArgumentException(
                static::class . ' only supports ' . ApplicationEnvironment::class
                . ', ' . get_class($object) . ' given.'
            );
        }
        $appEnv = $object;
        /** @var Environment $environment */
        $environment = $appEnv->getEnvironment();
        /** @var AbstractApplication $application */
        $application = $appEnv->getApplication();

        /** @var VirtualServer[] $servers */
        $servers = $environment->getVirtualServers();

        foreach ($servers as $server) {
            if (!$server->isTaskServer()) {
                continue;
            }

            $user = $application->getNameCanonical();
            $keyLocation = rtrim(Path::getHomeDirectory(), '/') . '/.ssh/id_rsa';

            $ssh = new SSH2($server->getHost(), $server->getPort());
            $key = new RSA();
            $key->loadKey(file_get_contents($keyLocation));

            if (!$ssh->login($user, $key)) {
                throw new \Exception('SSH login failed.');
            }
            return new RemoteCli($ssh);
        }
        return null;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\CLI;

use DigipolisGent\Domainator9k\CoreBundle\CLI\CliFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\CLI\CliInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Webmozart\PathUtil\Path;

class DefaultCliFactory implements CliFactoryInterface
{
    /**
     * {@inheritdoc}
     */
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

            $host = $server->getHost();
            $port = $server->getPort() ?: 22;
            $ssh = new SSH2($host, $port);
            $key = PublicKeyLoader::load(file_get_contents($keyLocation));

            if (!$ssh->login($user, $key)) {
                throw new \Exception(sprintf('SSH login for %s@%s:%s failed.', $user, $host, $port));
            }
            return new RemoteCli($ssh);
        }
        return null;
    }
}

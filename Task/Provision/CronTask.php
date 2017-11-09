<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskFactoryAware;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskFactoryAwareInterface;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CronTask extends AbstractTask implements TaskFactoryAwareInterface
{

    use TaskFactoryAware;

    /**
     * @return string
     */
    public static function getName()
    {
        return 'provision.cron';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array('servers', 'applicationTypeBuilder'));
    }

    public function execute()
    {
        $this->executed = true;

        /** @var AppEnvironment $appEnvironment */
        $appEnvironment = $this->options['appEnvironment'];
        /** @var Server[] $servers */
        $servers = $this->options['servers'];
        $user = $this->appEnvironment->getServerSettings()->getUser();
        $appFolder = $this->appEnvironment->getApplication()->getNameForFolder();
        $appPath = "/home/$user/apps/$appFolder/current";
        $taskRunner = $this->taskFactory->createRunner();
        $keyFilePath = $this->getHomeDirectory().'/.ssh/id_rsa';
        $keyFile = realpath($keyFilePath);

        if (!file_exists($keyFile)) {
            throw new RuntimeException(sprintf("private keyfile '%s' doesn't seem to exist", $keyFilePath));
        }

        foreach ($servers as $server) {
            if (!$server->isTaskServer()) {
                continue;
            }
            $taskRunner->addTask($this->taskFactory->create('console.cron', array(
                'AppEnvironment' => $appEnvironment,
                'host' => $server->getIp(),
                'user' => $user,
                'keyfile' => $keyFile,
                'cron' => str_replace('__APP__', $appPath, $appEnvironment->getApplication()->getCron()),
                'check' => true,
            )));
        }

        return $taskRunner->run();
    }
}

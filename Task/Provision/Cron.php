<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\KeyFile;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;
use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\Factory as TaskFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Cron extends AbstractTask
{
    /**
     * @return string
     */
    public function getName()
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
        $taskRunner = TaskFactory::createRunner();
        $keyFilePath = $this->getHomeDirectory().'/.ssh/id_rsa';
        $keyFile = realpath($keyFilePath);

        if (!file_exists($keyFile)) {
            throw new \RuntimeException(sprintf("private keyfile '%s' doesn't seem to exist", $keyFilePath));
        }

        $sshAuth = new KeyFile($user, $keyFile);
        foreach ($servers as $server) {
            if (!$server->isTaskServer()) {
                continue;
            }

            $ssh = new SshShell($server->getIp(), $sshAuth);
            $taskRunner->addTask(TaskFactory::create('console.cron', array(
                'AppEnvironment' => $appEnvironment,
                'shell' => $ssh,
                'cron' => str_replace('__APP__', $appPath, $appEnvironment->getApplication()->getCron()),
                'check' => true,
            )));
        }

        return $taskRunner->run();
    }
}

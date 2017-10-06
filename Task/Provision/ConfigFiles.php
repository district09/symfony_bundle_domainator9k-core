<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\KeyFile;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;
use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\Factory as TaskFactory;
use DigipolisGent\Domainator9k\CoreBundle\Task\Messenger;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigFiles extends AbstractTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'provision.settings_files';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array('servers', 'settings', 'applicationTypeBuilder'));
        $options->setAllowedTypes('settings', 'DigipolisGent\Domainator9k\CoreBundle\Entity\Settings');
    }

    public function execute()
    {
        $this->executed = true;

        /** @var ApplicationTypeBuilder $appTypeBuilder */
        $appTypeBuilder = $this->options['applicationTypeBuilder'];

        /** @var Settings $settings */
        $settings = $this->options['settings'];
        /** @var AppEnvironment $appEnvironment */
        $appEnvironment = $this->options['appEnvironment'];
        /** @var Server[] $servers */
        $servers = $this->options['servers'];
        $user = $this->appEnvironment->getServerSettings()->getUser();
        $taskRunner = new TaskRunner();
        $keyFilePath = $this->getHomeDirectory().'/.ssh/id_rsa';
        $keyFile = realpath($keyFilePath);

        if (!file_exists($keyFile)) {
            throw new \RuntimeException(sprintf("private keyfile '%s' doesn't seem to exist", $keyFilePath));
        }

        $files = $appTypeBuilder->getType($appEnvironment->getApplication()->getAppTypeSlug())->getConfigFiles($appEnvironment, $servers, $settings);

        $sshAuth = new KeyFile($user, $keyFile);
        foreach ($servers as $server) {
            $ssh = new SshShell($server->getIp(), $sshAuth);

            // establish ssh connection to server prematurely
            Messenger::send(sprintf(
                'connecting to %s@%s with private key %s',
                $user, $server->getIp(), $keyFile
            ));
            $ssh->connect();

            foreach ($files as $path => $content) {
                $taskRunner->addTask(TaskFactory::create('filesystem.create_file', array(
                    'appEnvironment' => $appEnvironment,
                    'shell' => $ssh,
                    'path' => $path,
                    'content' => $content,
                )));
            }
        }

        return $taskRunner->run();
    }
}

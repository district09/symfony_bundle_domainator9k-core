<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Auth\KeyFile;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;
use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\Factory as TaskFactory;
use DigipolisGent\Domainator9k\CoreBundle\Task\Messenger;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskRunner;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Filesystem extends AbstractTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'provision.filesystem';
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
        $appFolder = $this->appEnvironment->getApplication()->getNameForFolder();
        $taskRunner = new TaskRunner();
        $keyFilePath = $this->getHomeDirectory().'/.ssh/id_rsa';
        $keyFile = realpath($keyFilePath);

        if (!file_exists($keyFile)) {
            throw new \RuntimeException(sprintf("private keyfile '%s' doesn't seem to exist", $keyFilePath));
        }

        $appType = $appTypeBuilder->getType($appEnvironment->getApplication()->getAppTypeSlug());

        // directory structure
        $directories = array_merge(
            array(
                "/dist/$user/$appFolder/files/public",
                "/dist/$user/$appFolder/files/private",
                "/dist/$user/$appFolder/config",
                "/home/$user/apps/$appFolder/releases",
                "/home/$user/apps/$appFolder/backups",
                "/home/$user/apps/$appFolder/files/tmp",
            ),
            $appType->getDirectories($user)
        );

        // symlinks
        $links = array(
            "/home/$user/apps/$appFolder/files/public" => "/dist/$user/$appFolder/files/public",
            "/home/$user/apps/$appFolder/files/private" => "/dist/$user/$appFolder/files/private",
            "/home/$user/apps/$appFolder/config" => "/dist/$user/$appFolder/config",
            "/home/$user/apps/$appFolder/current" => "/home/$user/apps/$appFolder/releases/current",
        );

        $sshAuth = new KeyFile($user, $keyFile);
        foreach ($servers as $server) {
            $ssh = new SshShell($server->getIp(), $sshAuth);

            // establish ssh connection to server prematurely
            Messenger::send(sprintf(
                'connecting to %s@%s with private key %s',
                $user, $server->getIp(), $keyFile
            ));
            $ssh->connect();

            foreach ($directories as $dir) {
                $taskRunner->addTask(TaskFactory::create('filesystem.create_directory', array(
                    'appEnvironment' => $appEnvironment,
                    'shell' => $ssh,
                    'directory' => $dir,
                )));
            }
            foreach ($links as $name => $target) {
                $taskRunner->addTask(TaskFactory::create('filesystem.link', array(
                    'appEnvironment' => $appEnvironment,
                    'shell' => $ssh,
                    'name' => $name,
                    'target' => $target,
                )));
            }
        }

        return $taskRunner->run();
    }
}

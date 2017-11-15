<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskFactoryAware;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskFactoryAwareInterface;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilesystemTask extends AbstractTask implements TaskFactoryAwareInterface
{
    use TaskFactoryAware;

    /**
     * @return string
     */
    public static function getName()
    {
        return 'provision.filesystem';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array('servers', 'settings', 'applicationTypeBuilder'));
        $options->setAllowedTypes('settings', Settings::class);
    }

    public function execute()
    {
        $this->executed = true;

        /** @var ApplicationTypeBuilder $appTypeBuilder */
        $appTypeBuilder = $this->options['applicationTypeBuilder'];

        /** @var AppEnvironment $appEnvironment */
        $appEnvironment = $this->options['appEnvironment'];
        /** @var Server[] $servers */
        $servers = $this->options['servers'];
        $user = $appEnvironment->getServerSettings()->getUser();
        $application = $appEnvironment->getApplication();
        $appFolder = $application->getNameForFolder();
        $taskRunner = $this->taskFactory->createRunner();
        $keyFilePath = $this->getHomeDirectory() . '/.ssh/id_rsa';
        $keyFile = realpath($keyFilePath);

        if (!file_exists($keyFile)) {
            throw new RuntimeException(sprintf("private keyfile '%s' doesn't seem to exist", $keyFilePath));
        }

        $appType = $appTypeBuilder->getType($application->getAppTypeSlug());

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

        foreach ($servers as $server) {
            $ip = $server->getIp();
            foreach ($directories as $dir) {
                $taskRunner->addTask($this->taskFactory->create('filesystem.create_directory', array(
                    'appEnvironment' => $appEnvironment,
                    'host' => $ip,
                    'user' => $user,
                    'keyfile' => $keyFile,
                    'directory' => $dir,
                )));
            }
            foreach ($links as $name => $target) {
                $taskRunner->addTask($this->taskFactory->create('filesystem.link', array(
                    'appEnvironment' => $appEnvironment,
                    'host' => $ip,
                    'user' => $user,
                    'keyfile' => $keyFile,
                    'name' => $name,
                    'target' => $target,
                )));
            }
        }

        return $taskRunner->run();
    }
}

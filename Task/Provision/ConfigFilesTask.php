<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Provision;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Service\ApplicationTypeBuilder;
use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractTask;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskFactoryAware;
use DigipolisGent\Domainator9k\CoreBundle\Task\TaskFactoryAwareInterface;
use DigipolisGent\SockAPIBundle\JsonModel\Server;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigFilesTask extends AbstractTask implements TaskFactoryAwareInterface
{
    use TaskFactoryAware;

    /**
     * @return string
     */
    public static function getName()
    {
        return 'provision.config_files';
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

        /** @var Settings $settings */
        $settings = $this->options['settings'];
        /** @var AppEnvironment $appEnvironment */
        $appEnvironment = $this->options['appEnvironment'];
        /** @var Server[] $servers */
        $servers = $this->options['servers'];
        $user = $appEnvironment->getServerSettings()->getUser();
        $taskRunner = $this->taskFactory->createRunner();
        $keyFilePath = $this->getHomeDirectory() . '/.ssh/id_rsa';
        $keyFile = realpath($keyFilePath);

        if (!file_exists($keyFile)) {
            throw new RuntimeException(sprintf("private keyfile '%s' doesn't seem to exist", $keyFilePath));
        }

        $files = $appTypeBuilder
            ->getType($appEnvironment->getApplication()->getAppTypeSlug())
            ->getConfigFiles($appEnvironment, $servers, $settings);

        foreach ($servers as $server) {
            foreach ($files as $path => $content) {
                $taskRunner->addTask(
                    $this->taskFactory->create(
                        'filesystem.create_file',
                        array(
                            'appEnvironment' => $appEnvironment,
                            'host' => $server->getIp(),
                            'user' => $user,
                            'keyfile' => $keyFile,
                            'path' => $path,
                            'content' => $content,
                        )
                    )
                );
            }
        }

        return $taskRunner->run();
    }
}

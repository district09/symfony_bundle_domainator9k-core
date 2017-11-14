<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Command;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\EntityService\BuildService;
use DigipolisGent\Domainator9k\CoreBundle\Task\Messenger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProvisionCommand extends AbstractBuildCommand
{
    /**
     * @var \DigipolisGent\Domainator9k\CoreBundle\EntityService\SettingsService
     */
    protected $settingsService;

    /**
     * @var \DigipolisGent\Domainator9k\CoreBundle\EntityService\ServerService
     */
    protected $serverService;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->settingsService = $this->getContainer()->get('digip_deploy.entity.settings');
        $this->serverService = $this->getContainer()->get('digip_deploy.entity.server');
    }

    protected function configure()
    {
        $this
            ->setName('digip:provision')
            ->setDescription('provision an application\'s servers')
            ->addArgument(
                'application',
                InputArgument::OPTIONAL,
                'For which application do you want to provision servers?'
            )
            ->addOption(
                'build',
                'b',
                InputOption::VALUE_OPTIONAL,
                'A queued build you want to start instead of creating a new one'
            )

            // options for partials builds
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Run all parts of the provisioning')
            ->addOption('ci', 'j', InputOption::VALUE_NONE, 'Run ci related parts of the provisioning')
            ->addOption('ci-override', 'J', InputOption::VALUE_NONE, 'Run ci related parts of the provisioning (override if ci jobs exist)')
            ->addOption('filesystem', 'f', InputOption::VALUE_NONE, 'Run filesystem related parts of the provisioning')
            ->addOption('config', 'c', InputOption::VALUE_NONE, 'Run config file related parts of the provisioning')
            ->addOption('sock', 'S', InputOption::VALUE_NONE, 'Run sock related parts of the provisioning')
            ->addOption('cron', 'C', InputOption::VALUE_NONE, 'Run cron related parts of the provisioning')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Messenger::addListener(function ($message) use ($output) {
            // @codeCoverageIgnoreStart
            $output->writeln($message);
            // @codeCoverageIgnoreEnd
        });

        if ($input->getOption('build')) {
            /** @var Build $build */
            $build = $this->loadBuild($input->getOption('build'));
        } else {
            if ($input->getArgument('application')) {
                $application = $this->loadApplication($input->getArgument('application'));
            } else {
                $application = $this->askApplication($input, $output);
            }
            $build = new Build($application, Build::TYPE_PROVISION);
            $application->setProvisionBuild($build);
            $this->buildService->persist($build);
        }

        $build->setPid(getmypid());
        $this->buildService->persist($build);

        $this->doBuild($build, $input);
    }

    protected function doBuild(Build $build, InputInterface $input)
    {
        $settings = $this->settingsService->getSettings();

        $mask = 0;
        $parts = false;

        // should be based on ciType
        if ($input->getOption('ci')) {
            $mask |= BuildService::PROVISION_CI;
            $parts = true;
        }
        if ($input->getOption('ci-override')) {
            $mask |= BuildService::PROVISION_CI_OVERRIDE;
            $parts = true;
        }
        // should be based on ciType

        if ($input->getOption('filesystem')) {
            $mask |= BuildService::PROVISION_FILESYSTEM;
            $parts = true;
        }
        if ($input->getOption('config')) {
            $mask |= BuildService::PROVISION_CONFIG_FILES;
            $parts = true;
        }
        if ($input->getOption('sock')) {
            $mask |= BuildService::PROVISION_SOCK;
            $parts = true;
        }
        if ($input->getOption('cron')) {
            $mask |= BuildService::PROVISION_CRON;
            $parts = true;
        }
        if (!$parts || $input->getOption('all')) {
            $mask = BuildService::PROVISION_ALL;
        }

        $result = $this->buildService->execute(
            $build,
            $this->serverService->getFinder()->find()->getAll(),
            $settings,
            $mask
        );

        return $result ? 0 : 1;
    }
}

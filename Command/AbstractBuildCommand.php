<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Command;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBuildCommand extends ContainerAwareCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Application[]
     */
    protected $applications;

    /**
     * @var \DigipolisGent\Domainator9k\CoreBundle\EntityService\BuildService
     */
    protected $buildService;

    /**
     * @var \DigipolisGent\Domainator9k\CoreBundle\EntityService\ApplicationService
     */
    protected $applicationService;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->buildService = $this->getContainer()->get('digip_deploy.entity.build');
        $this->applicationService = $this->getContainer()->get('digip_deploy.entity.application');
    }

    public function loadBuild($id)
    {
        /** @var Build $build */
        $build = $this->buildService->getFinder()->get($id);

        if ($build->isStarted()) {
            throw new \InvalidArgumentException(sprintf(
                'build %s was already started',
                $build->getId()
            ));
        }

        return $build;
    }

    public function getApplications()
    {
        if (!$this->applications) {
            $this->applications = $this->applicationService->getFinder()->find()->getAll();
        }

        return $this->applications;
    }

    public function loadApplication($id)
    {
        return $this->applicationService->getFinder()->get($id);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Application
     */
    protected function askApplication(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $dialog */
        $dialog = $this->getHelper('question');

        $choices = array();
        foreach ($this->getApplications() as $app) {
            $choices[$app->getId()] = $app->getName();
        }

        $question = new ChoiceQuestion('Which application would you like to deploy?', $choices);
        $result = $dialog->ask($input, $output, $question);

        // The ChoiceQuestion class handles invalid answers, so the chosen
        // application will always exist.
        foreach ($this->getApplications() as $app) {
            if ($app->getName() === $result) {
                break;
            }
        }

        return $app;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Command;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBuildCommand extends Command implements ContainerAwareInterface
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
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function loadBuild($id)
    {
        $buildService = $this->container->get('digip_deploy.entity.build');

        /** @var Build $build */
        $build = $buildService->getFinder()->get($id);

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
            $this->applications = $this->container->get('digip_deploy.entity.application')->getFinder()->find()->getAll();
        }

        return $this->applications;
    }

    public function loadApplication($id)
    {
        return $this->container->get('digip_deploy.entity.application')->getFinder()->get($id);
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

        foreach ($this->getApplications() as $app) {
            if ($app->getName() === $result) {
                return $app;
            }
        }

        $output->writeln('something went wrong with the selection, please try again');

        return $this->askApplication($input, $output);
    }
}

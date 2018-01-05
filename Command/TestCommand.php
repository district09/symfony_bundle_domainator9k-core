<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Command;


use DigipolisGent\Domainator9k\CiTypes\JenkinsBundle\Entity\JenkinsGroovyScript;
use DigipolisGent\Domainator9k\CiTypes\JenkinsBundle\Entity\JenkinsJob;
use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand
 * @package DigipolisGent\Domainator9k\CoreBundle\Command
 */
class TestCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('domainator:test');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var TemplateService $templateService */
        $templateService = $this->getContainer()->get(TemplateService::class);

        $groovyScripts = $entityManager->getRepository(JenkinsGroovyScript::class)->findAll();
        $groovyScript = end($groovyScripts);

        $applications = $entityManager->getRepository(AbstractApplication::class)->findAll();
        $application = end($applications);

        $applicationEnvironments = $entityManager->getRepository(ApplicationEnvironment::class)->findAll();
        $applicationEnvironment = end($applicationEnvironments);

        $jenkinsJobs = $entityManager->getRepository(JenkinsJob::class)->findAll();
        $jenkinsJob = end($jenkinsJobs);

        $entities = [
            'application' => $application,
            'application_environment' => $applicationEnvironment,
            'jenkins_job' => $jenkinsJob,
        ];

        $text = $templateService->replaceKeys($groovyScript, 'getContent', $entities);
        dump($text);
        die();
    }
}
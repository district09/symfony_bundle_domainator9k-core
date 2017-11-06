<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Service\EnvironmentService;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;

class Mail
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var EnvironmentService
     */
    private $environmentService;

    /**
     * Mail constructor.
     *
     * @param \Swift_Mailer      $mailer
     * @param EnvironmentService $environmentService
     */
    public function __construct(\Swift_Mailer $mailer, EnvironmentService $environmentService)
    {
        $this->mailer = $mailer;
        $this->environmentService = $environmentService;
    }

    /**
     * @param Settings       $settings
     * @param Application    $application
     * @param array|Server[] $servers
     *
     * @return bool
     */
    public function sendDnsMail(Settings $settings, Application $application, array $servers)
    {
        $template = $application->getDnsMailTemplate();
        if (!$template) {
            throw new \RuntimeException('DNS mail template is empty');
        }

        $recipients = trim($settings->getDnsMailRecipients());
        $emails = explode(',', $recipients);
        if (empty($recipients) || !count($emails)) {
            return false;
        }

        $ip = [];
        foreach ($this->environmentService->getEnvironments() as $environment) {
            $ip[$environment->getName()] = '';
        }
        $domain = [];
        foreach ($this->environmentService->getEnvironments() as $environment) {
            $domain[$environment->getName()] = '';
        }

        foreach ($servers as $s) {
            if ($s->manageSock()) {
                $ip[$s->getEnvironment()] = $s->getIp();
            }
        }
        foreach ($application->getAppEnvironments() as $e) {
            $domain[$e->getNameCanonical()] = $e->getPreferredDomain();
        }

        $content = str_replace(
            array(
                '[[URL_TEST]]',
                '[[URL_QA]]',
                '[[URL_PROD]]',
                '[[IP_TEST]]',
                '[[IP_QA]]',
                '[[IP_PROD]]',
            ),
            array(
                $domain['test'],
                $domain['qa'],
                $domain['prod'],
                $ip['test'],
                $ip['qa'],
                $ip['prod'],
            ),
            $template
        );

        $message = \Swift_Message::newInstance('DNS record request', $content)
            ->setFrom('tbweb@digipolis.gent', 'Digipolis domein web');

        foreach ($emails as $r) {
            if (!empty($r)) {
                $message->addTo($r);
            }
        }

        $this->mailer->send($message);

        $application->setDnsMailSent(true);

        return true;
    }
}

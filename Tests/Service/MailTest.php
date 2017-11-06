<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Settings;
use DigipolisGent\Domainator9k\CoreBundle\Service\EnvironmentService;
use DigipolisGent\Domainator9k\CoreBundle\Service\Mail;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use RuntimeException;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of MailTest
 *
 * @author Jelle Sebreghts
 */
class MailTest extends TestCase
{

    use DataGenerator;

    protected $mailer;
    protected $environmentService;

    protected function setUp()
    {
        parent::setUp();
        $this->mailer = $this->getMockBuilder(Swift_Mailer::class)->disableOriginalConstructor()->getMock();
        $this->environmentService = $this->getMockBuilder(EnvironmentService::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage DNS mail template is empty
     */
    public function testSendDnsMailNoTemplate()
    {
        $service = $this->getService();

        $settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getDnsMailTemplate')->willReturn(false);

        $service->sendDnsMail($settings, $app, []);
    }

    public function testSendDnsMailNoRecipients()
    {
        $service = $this->getService();

        $template = $this->getAlphaNumeric();

        $settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('getDnsMailRecipients')->willReturn('');

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getDnsMailTemplate')->willReturn($template);

       $this->assertFalse($service->sendDnsMail($settings, $app, []));
    }

    public function testSendDnsMail()
    {
        $service = $this->getService();

        $mails = [$this->getAlphaNumeric() . '@gmail.com' => null, $this->getAlphaNumeric() . '@outlook.com' => null];

        $settings = $this->getMockBuilder(Settings::class)->disableOriginalConstructor()->getMock();
        $settings->expects($this->once())->method('getDnsMailRecipients')->willReturn(implode(',', array_keys($mails)));

        $test = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $test->expects($this->any())->method('getName')->willReturn('test');
        $qa = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $qa->expects($this->any())->method('getName')->willReturn('qa');
        $prod = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $prod->expects($this->any())->method('getName')->willReturn('prod');

        $testIp = $this->getAlphaNumeric();
        $qaIp = $this->getAlphaNumeric();
        $prodIp = $this->getAlphaNumeric();

        $testServer = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $testServer->expects($this->once())->method('manageSock')->willReturn(true);
        $testServer->expects($this->once())->method('getEnvironment')->willReturn('test');
        $testServer->expects($this->once())->method('getIp')->willReturn($testIp);

        $qaServer = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $qaServer->expects($this->once())->method('manageSock')->willReturn(true);
        $qaServer->expects($this->once())->method('getEnvironment')->willReturn('qa');
        $qaServer->expects($this->once())->method('getIp')->willReturn($qaIp);

        $prodServer = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $prodServer->expects($this->once())->method('manageSock')->willReturn(true);
        $prodServer->expects($this->once())->method('getEnvironment')->willReturn('prod');
        $prodServer->expects($this->once())->method('getIp')->willReturn($prodIp);

        $testDomain = $this->getAlphaNumeric();
        $qaDomain = $this->getAlphaNumeric();
        $prodDomain = $this->getAlphaNumeric();

        $testAppEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $testAppEnv->expects($this->any())->method('getNameCanonical')->willReturn('test');
        $testAppEnv->expects($this->any())->method('getPreferredDomain')->willReturn($testDomain);
        $qaAppEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $qaAppEnv->expects($this->any())->method('getNameCanonical')->willReturn('qa');
        $qaAppEnv->expects($this->any())->method('getPreferredDomain')->willReturn($qaDomain);
        $prodAppEnv = $this->getMockBuilder(AppEnvironment::class)->disableOriginalConstructor()->getMock();
        $prodAppEnv->expects($this->any())->method('getNameCanonical')->willReturn('prod');
        $prodAppEnv->expects($this->any())->method('getPreferredDomain')->willReturn($prodDomain);

        $template = 'URL TEST: [[URL_TEST]] URL QA: [[URL_QA]] URL PROD: [[URL_PROD]] IP TEST: [[IP_TEST]] IP QA: [[IP_QA]] IP PROD: [[IP_PROD]]';
        $expected = 'URL TEST: ' . $testDomain . ' URL QA: ' . $qaDomain . ' URL PROD: ' . $prodDomain . ' IP TEST: ' . $testIp . ' IP QA: ' . $qaIp . ' IP PROD: ' . $prodIp;

        $app = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $app->expects($this->once())->method('getDnsMailTemplate')->willReturn($template);
        $app->expects($this->once())->method('setDnsMailSent')->with(true);
        $app->expects($this->once())->method('getAppEnvironments')->willReturn([$testAppEnv, $qaAppEnv, $prodAppEnv]);

        $this->environmentService->expects($this->exactly(2))->method('getEnvironments')->willReturn([$test, $qa, $prod]);

        $this->mailer->expects($this->once())->method('send')->with($this->callback(function (\Swift_Message $message) use ($expected, $mails) {
            return $message->getBody() === $expected && $message->getFrom() === ['tbweb@digipolis.gent' => 'Digipolis domein web'] && $message->getTo() === $mails;
        }));

        $this->assertTrue($service->sendDnsMail($settings, $app, [$testServer, $qaServer, $prodServer]));
    }

    protected function getService()
    {
        return new Mail($this->mailer, $this->environmentService);
    }

}

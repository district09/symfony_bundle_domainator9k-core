<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSshTask extends AbstractTask implements SshTaskInterface
{
    /**
     * @var SshShellFactoryInterface
     */
    protected $sshShellFactory;

    /**
     * @var SshShellInterface
     */
    protected $shell;

    /**
     * Assert a shell has been created, create one if not.
     */
    protected function assertShell()
    {
        if (!$this->shell instanceof SshShellInterface) {
            $password = isset($this->options['password'])
                ? $this->options['password']
                : (isset($this->options['keyfile'])
                    ? $this->options['keyfile']
                    : null
                );
            $authType = isset($this->options['authtype'])
                ? $this->options['authtype']
                : (isset($this->options['password'])
                    ? SshShellFactory::AUTH_TYPE_CREDENTIALS
                    : SshShellFactory::AUTH_TYPE_KEY
                );
            $this->shell = $this->sshShellFactory->create(
                $this->options['host'],
                $authType,
                $this->options['user'],
                $password
            );
        }
    }

    /**
     * @param OptionsResolver $options
     */
    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);
        $options->setRequired(array(
            'user',
            'host',
        ));
        $options->setDefined(['authtype', 'password', 'keyfile']);
        $options->setAllowedTypes('user', ['string']);
        $options->setAllowedTypes('host', ['string']);
        $options->setAllowedTypes('authtype', ['string']);
        $options->setAllowedTypes('password', ['string']);
        $options->setAllowedTypes('keyfile', ['string']);
    }

    public function execute()
    {
        $result = parent::execute();
        $this->assertShell();

        return $result;
    }

    protected function doExec(TaskResult $result, $command, &$stdout = null, &$exitStatus = null, &$stderr = null)
    {
        $result->addMessage(sprintf('EXEC %s', $command));
        $this->shell->exec($command, $stdout, $exitStatus, $stderr);

        $result->setSuccess(0 === $exitStatus);
        $result->setData($stdout);
        if (!$result->isSuccess()) {
            $result->addMessage($stdout);
            $result->addMessage($stderr);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setSshShellFactory(SshShellFactoryInterface $sshShellFactory)
    {
        $this->sshShellFactory = $sshShellFactory;

        return $this;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AppEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactory;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\Factory\SshShellFactoryInterface;
use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShellInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSshTask extends AbstractTask
{
    /**
     * @var bool
     */
    protected $executed = false;

    protected $options = array();

    /**
     * @var SshShellFactoryInterface
     */
    protected $sshShellFactory;

    /**
     * @var AppEnvironment
     */
    protected $appEnvironment;

    /**
     * @var SshShellInterface
     */
    protected $shell;

    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $password = isset($this->options['password'])
            ? $this->options['password']
            : (isset($this->options['keyfile'])
                ? $this->options['keyfile']
                : null
            );
        $authType = isset($this->options['authtype'])
            ? $this->options['authtype']
            : (isset($this->options['keyfile'])
                ? SshShellFactory::AUTH_TYPE_KEY
                : SshShellFactory::AUTH_TYPE_CREDENTIALS
            );
        $this->shell = $this->sshShellFactory->create($this->options['host'], $authType, $this->options['user'], $password);
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);
        $options->setRequired(array(
            'user',
            'host',
        ));
        $options->setAllowedTypes('user', ['string']);
        $options->setAllowedTypes('host', ['string']);
        $options->setAllowedTypes('authtype', ['string']);
        $options->setAllowedTypes('password', ['string']);
        $options->setAllowedTypes('keyfile', ['string']);
    }

    protected function doExec(TaskResult $result, $command, &$stdout = null, &$exitStatus = null, &$stderr = null)
    {
        $result->addMessage(sprintf('EXEC %s', $command));

        $this->shell->exec($command, $stdout, $exitStatus, $stderr);

        $result->setSuccess($exitStatus === 0);
        $result->setData($stdout);
        if (!$result->isSuccess()) {
            $result->addMessage($stdout);
            $result->addMessage($stderr);
        }

        return $result;
    }
}

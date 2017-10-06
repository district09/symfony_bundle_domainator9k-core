<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

use DigipolisGent\Domainator9k\CoreBundle\Ssh\SshShell;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSshTask extends AbstractTask
{
    /**
     * @var SshShell
     */
    protected $shell;

    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->shell = $this->options['shell'];
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired('shell');
        $options->setAllowedTypes('shell', 'DigipolisGent\Domainator9k\CoreBundle\Ssh\ShellInterface');
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

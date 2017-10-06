<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem;

use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractSshTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateFile extends AbstractSshTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'filesystem.create_file';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array(
            'path',
            'content',
        ));

        $options->setAllowedTypes('path', 'string');
        $options->setAllowedTypes('content', 'string');
    }

    public function execute()
    {
        $result = parent::execute();
        $path = escapeshellarg($this->options['path']);
        $content = escapeshellarg($this->options['content']);

        $this->shell->connect();

        $cmd = "echo $content > $path";
        $this->doExec($result, $cmd);
        $result->addMessage(sprintf('%s creating %s', $result->isSuccess() ? 'SUCCESS' : 'FAILED', $path));

        return $result;
    }
}

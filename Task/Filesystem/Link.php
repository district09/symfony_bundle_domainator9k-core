<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem;

use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractSshTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Link extends AbstractSshTask
{
    /**
     * @return string
     */
    public static function getName()
    {
        return 'filesystem.link';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array(
            'name',
            'target',
        ));

        $options->setAllowedTypes('name', 'string');
        $options->setAllowedTypes('target', 'string');
    }

    public function execute()
    {
        $result = parent::execute();
        $name = $this->options['name'];
        $target = $this->options['target'];

        $cmd = "ln -sfn $target $name";
        $this->doExec($result, $cmd);
        $result->addMessage(sprintf('%s linking from %s to %s', $result->isSuccess() ? 'SUCCESS' : 'FAILED', $name, $target));

        return $result;
    }
}

<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractSshTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractConsole extends AbstractSshTask
{
    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array('command', 'directory'));
    }
}

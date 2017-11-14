<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Filesystem;

use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractSshTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateDirectoryTask extends AbstractSshTask
{
    /**
     * @return string
     */
    public static function getName()
    {
        return 'filesystem.create_directory';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array(
            'directory',
        ));

        $options->setAllowedTypes('directory', 'string');
    }

    public function execute()
    {
        $result = parent::execute();

        $result->addMessage(sprintf('creating directory %s', $this->options['directory']));
        if (!$this->shell->fileExists($this->options['directory'])) {
            $result->setData($this->shell->mkdir($this->options['directory'], 0755, true));

            if (!$result->getData()) {
                $result->setSuccess(false);
                $result->addMessage(sprintf('FAILED creating directory %s', $this->options['directory']));

                return $result;
            }

            $result->addMessage(sprintf('SUCCESS creating directory %s', $this->options['directory']));

            return $result;
        }

        $result->addMessage(sprintf('SUCCESS creating directory %s, directory already exists', $this->options['directory']));

        return $result;
    }
}

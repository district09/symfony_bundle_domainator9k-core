<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Task\AbstractSshTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Cron extends AbstractSshTask
{
    /**
     * @return string
     */
    public static function getName()
    {
        return 'console.cron';
    }

    protected function configure(OptionsResolver $options)
    {
        parent::configure($options);

        $options->setRequired(array(
            'cron',
        ));
        $options->setDefaults(array(
            'check' => false,
        ));

        $options->setAllowedTypes('cron', 'string');
    }

    public function execute()
    {
        $result = parent::execute();
        $cron = $this->options['cron'];
        $check = $this->options['check'];

        if ($check === true) {
            $lines = explode(PHP_EOL, $cron);
            $regex = [];
            foreach ($lines as $l) {
                $regex[] = preg_quote(trim($l));
            }
            $regex = '('.implode(')|(', $regex).')';

            $check = "| grep -vE \"$regex\"";
        } elseif ($check !== false) {
            $check = "| grep -vE \"$check\"";
        }

        $cmd = "(crontab -l $check; echo \"$cron\") | crontab";
        $this->doExec($result, $cmd);
        $result->addMessage(sprintf('%s installed cron job %s', $result->isSuccess() ? 'SUCCESS' : 'FAILED', $cron));

        return $result;
    }
}

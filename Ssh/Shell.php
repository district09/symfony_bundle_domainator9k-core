<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Ssh;

class Shell implements ShellInterface
{
    /**
     * {@inheritdoc}
     */
    public function exec($command, &$stdout = null, &$exitStatus = null, &$stderr = null)
    {
        $descriptors = array(
            0 => array('pipe', 'r'),    // stdin
            1 => array('pipe', 'w'),    // stdout
            2 => array('pipe', 'w'),     // stderr
        );

        $pipes = array();
        $proc = proc_open($command, $descriptors, $pipes);

        if (is_resource($proc)) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitStatus = proc_close($proc);
        }

        return $exitStatus === 0;
    }
}

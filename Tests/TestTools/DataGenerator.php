<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools;

/**
 *
 * @author Jelle Sebreghts
 */
trait DataGenerator
{

    protected function getAlphaNumeric($withSymbols = false, $length = 0)
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . ($withSymbols ? '0123456789!@#$%^&*()_-' : ''));
        $invalidSeed = str_split('!@#$%^&*()_-');
        $name = '';
        shuffle($seed);
        shuffle($invalidSeed);
        foreach (array_rand($seed, mt_rand(5, count($seed))) as $k)
        {
            $name .= $seed[$k];
        }
        if ($length)
        {
            $length = min($length, strlen($name));
            $name = substr($name, 0, $withSymbols ? ($length - 1) : $length);
        }
        if ($withSymbols)
        {
            // Make sure we have at least one invalid character.
            $name .= $invalidSeed[array_rand($invalidSeed)];
        }
        return $name;
    }

}

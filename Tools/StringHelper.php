<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tools;

class StringHelper
{
    public static function canonicalize($string, $toLowerCase = true, $allowDash = false)
    {
        if ($toLowerCase) {
            $string = strtolower($string);
        }

        $allowed = 'a-zA-Z0-9';
        if ($allowDash) {
            $allowed .= '-';
        }

        return preg_replace("/[^$allowed]+/", '', $string);
    }
}

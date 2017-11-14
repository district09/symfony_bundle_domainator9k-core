<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

class Messenger
{
    /**
     * @var array|callable[]
     */
    protected static $listeners = array();

    /**
     * @param $callback
     * @param string|null $key
     */
    public static function addListener($callback, $key = null)
    {
        if ($key) {
            self::$listeners[$key] = $callback;
        } else {
            self::$listeners[] = $callback;
        }
    }

    /**
     * @param string|string[] $messages
     *
     * @throws \Exception
     */
    public static function send($messages)
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }

        foreach ($messages as $msg) {
            if (!is_string($msg)) {
                $type = gettype($msg);

                throw new \Exception(sprintf('Messenger can only send strings, got %s.', 'object' === $type ? get_class($msg) : $type));
            }

            foreach (self::$listeners as $listener) {
                call_user_func($listener, $msg);
            }
        }
    }
}

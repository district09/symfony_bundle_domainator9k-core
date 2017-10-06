<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Task;

class TaskResult
{
    /**
     * @var bool
     */
    protected $success = true;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     *
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    public function addMessage($message)
    {
        $this->messages[] = $message;

        Messenger::send($message);
    }
}

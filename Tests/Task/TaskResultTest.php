<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task;

use DigipolisGent\Domainator9k\CoreBundle\Task\TaskResult;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of TaskResultTest.
 *
 * @author Jelle Sebreghts
 */
class TaskResultTest extends TestCase
{
    use DataGenerator;

    public function testSetMessages()
    {
        $result = new TaskResult();
        $messages = [$this->getAlphaNumeric()];
        $this->assertEquals($result, $result->setMessages($messages));
        $this->assertEquals($messages, $result->getMessages());
    }
}

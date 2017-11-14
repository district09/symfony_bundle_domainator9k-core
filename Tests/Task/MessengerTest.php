<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task;

use DigipolisGent\Domainator9k\CoreBundle\Task\Messenger;
use DigipolisGent\Domainator9k\CoreBundle\Tests\TestTools\DataGenerator;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of MessengerTest
 *
 * @author Jelle Sebreghts
 */
class MessengerTest extends TestCase
{

    use DataGenerator;

    public function testMessenger() {
        $message = $this->getAlphaNumeric();
        $key = $this->getAlphaNumeric();

        $callback1 = $this->createPartialMock(stdClass::class, ['__invoke']);
        $callback1->expects($this->once())->method('__invoke')->with($message);

        $callback2 = $this->createPartialMock(stdClass::class, ['__invoke']);
        $callback2->expects($this->once())->method('__invoke')->with($message);

        $callback3 = $this->createPartialMock(stdClass::class, ['__invoke']);
        $callback3->expects($this->never())->method('__invoke');

        Messenger::addListener($callback1);
        Messenger::addListener($callback3, $key);
        Messenger::addListener($callback2, $key);
        Messenger::send($message);
    }

    /**
     * @expectedException \Exception
     */
    public function testMessengerNoString() {
        $message = new \stdClass();
        $key = $this->getAlphaNumeric();

        $callback = $this->createPartialMock(stdClass::class, ['__invoke']);
        $callback->expects($this->never())->method('__invoke');

        Messenger::addListener($callback);
        Messenger::send($message);
    }

}

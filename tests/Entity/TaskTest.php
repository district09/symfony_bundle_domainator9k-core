<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{

    public function testGettersAndSetters()
    {
        $task = new Task();

        $this->assertInstanceOf(\DateTime::class,$task->getCreated());
        $this->assertEquals('new',$task->getStatus());

        $task->setStatus(Task::STATUS_PROCESSED);
        $this->assertEquals('processed',$task->getStatus());

        $task->setType(Task::TYPE_BUILD);
        $this->assertEquals('build',$task->getType());

        $applicationEnvironment = new ApplicationEnvironment();
        $task->setApplicationEnvironment($applicationEnvironment);
        $this->assertEquals($applicationEnvironment,$task->getApplicationEnvironment());
    }

}

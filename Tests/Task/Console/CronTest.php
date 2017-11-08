<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Task\Console;

use DigipolisGent\Domainator9k\CoreBundle\Task\Console\Cron;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Description of CronTest
 *
 * @author Jelle Sebreghts
 */
class CronTest extends TestCase
{
    protected $options = [];
    protected $host;
    protected $password;
    protected $keyfile;
    protected $authtype;
    protected $user;

    protected function setUp()
    {
        parent::setUp();
        $this->options = [

        ];
    }

    public function testGetName() {
        $this->assertEquals('console.cron', Cron::getName());
    }

    protected function getCronTask() {
        $task = new Cron($this->options);
    }

}

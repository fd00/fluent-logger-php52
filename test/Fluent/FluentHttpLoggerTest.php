<?php
require_once dirname(__FILE__) . '/../../src/Fluent/HttpLogger.php';

class FluentHttpLoggerTest extends PHPUnit_Framework_TestCase
{
    private $pidFilePath;

    function setUp()
    {
        $this->pidFilePath = tempnam('/tmp', 'fluent.pid');
        $command = sprintf('bash --login -c "fluentd -c %s -d %s"', dirname(__FILE__) . '/fixture/fluent.conf', $this->pidFilePath);
        exec($command);
        sleep(1);
    }

    function tearDown()
    {
        posix_kill(file_get_contents($this->pidFilePath), 9);
        if (file_exists($this->pidFilePath)) {
            unlink($this->pidFilePath);
        }
    }

    function testSend()
    {
        $logger = new FluentHttpLogger('myapp', 'localhost');
        $logger->send(array('user' => 'abc', 'password' => 'def'));

        $logPaths = glob('/tmp/fluent.phpunit.*');
        foreach ($logPaths as $logPath) {
            $contents = file_get_contents($logPath);
            unlink($logPath);
            $this->assertSame(32, strpos($contents, '{"user":"abc","password":"def"}'));
            return;
        }
        unlink($logPath);
        $this->fail();
    }
}
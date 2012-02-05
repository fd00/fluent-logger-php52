<?php
require_once dirname(__FILE__) . '/../../src/Fluent/HttpLogger.php';

class FluentHttpLoggerTest extends PHPUnit_Framework_TestCase
{
    private $pidFilePath;

    function setUp()
    {
        $this->pidFilePath = tempnam('/tmp', 'fluentd.pid');
        $command = sprintf('bash --login -c "fluentd -c %s -d %s"', dirname(__FILE__) . '/fixture/fluent.http.conf', $this->pidFilePath);
        exec($command);
    }

    function tearDown()
    {
        if (file_exists($this->pidFilePath)) {
            $command = sprintf('kill %s', file_get_contents($this->pidFilePath));
            exec($command);
            unlink($this->pidFilePath);
        }

        $this->cleanup();
    }

    function testSend()
    {
        $this->cleanup();

        $logger = new FluentHttpLogger('phpunit.fluent', 'localhost');
        $logger->send(array('user' => 'abc', 'password' => 'def'));
        sleep(1);

        $logPaths = glob('/tmp/fluentd.phpunit.*');
        if (count($logPaths) !== 1) {
            $this->fail();
        }
        $logPath = $logPaths[0];
        $contents = file_get_contents($logPath);
        if (preg_match('/^(?P<time>.*)\t(?P<tag>.*)\t(?P<data>.*)$/', $contents, $matches) !== 1) {
            $this->fail();
        }
        $this->assertSame('{"user":"abc","password":"def"}', $matches['data']);
    }

    private function cleanup()
    {
        $logPaths = glob('/tmp/fluentd.phpunit.*');
        if (!empty($logPaths)) {
            foreach ($logPaths as $logPath) {
                unlink($logPath);
            }
        }
    }
}
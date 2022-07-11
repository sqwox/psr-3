<?php

require_once 'index.php';
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{

    public function testInterpolate()
    {
        $logFileName = dirname(__DIR__) . '/' . 'tests' . '/'  . date('Y-m-d') . '.log';
        $handler = new FileHandler($logFileName);
        $my = new Logger($handler);
        $context = [
          'test' => 'ok'
        ];
        $this->assertEquals('ok', $my->interpolate('{test}', $context));
    }

}

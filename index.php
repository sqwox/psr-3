<?php
require 'vendor/autoload.php';

use Psr\Log\AbstractLogger;

interface HandlerInterface
{
    public const DEFAULT_FORMAT = '%timestamp% [%level%]: %message%';

    public function handle(array $vars): void;
}

class Logger extends AbstractLogger
{
    protected const DEFAULT_DATETIME_FORMAT = 'c';

    /**
     * @var HandlerInterface
     */
    private $handler;

    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function log($level, $message, array $context = array())
    {
        $this->handler->handle([
            'message' => self::interpolate((string)$message, $context),
            'level' => strtoupper($level),
            'timestamp' => (new \DateTimeImmutable())->format(self::DEFAULT_DATETIME_FORMAT),
        ]);
    }

    public static function interpolate(string $message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || method_exists($val, '__toString')) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
}

class FileHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private $filename;

    public function __construct(string $filename)
    {
        $dir = dirname($filename);
        if (!file_exists($dir)) {
            $status = mkdir($dir, 0777, true);
            if ($status === false && !is_dir($dir)) {
                throw new \UnexpectedValueException(sprintf('error directory', $dir));
            }
        }
        $this->filename = $filename;
    }

    public function handle(array $vars): void
    {
        $output = self::DEFAULT_FORMAT;
        foreach ($vars as $var => $val) {
            $output = str_replace('%' . $var . '%', $val, $output);
        }
        file_put_contents($this->filename, $output . PHP_EOL, FILE_APPEND);
    }
}

$logFileName = dirname(__DIR__) . '/' . 'log' . '/' . 'ready' . '/' . date('Y-m-d') . '.log';

$handler = new FileHandler($logFileName);
$logger = new Logger($handler);

$logger->emergency('emergency');
echo 'emergency log send  <br>';
$logger->alert('alert');
echo 'alert log send  <br>';
$logger->critical('critical');
echo 'critical log send  <br>';
$logger->error('error');
echo 'error log send <br>';

$logger->warning('warning');
echo 'warning log send  <br>';
$logger->notice('notice');
echo 'notice log send  <br>';

$logger->info('info');
echo 'info log send  <br>';
$logger->debug('debug');
echo 'debug log send <br>';


$msg = '
SOFTWARE - {SERVER_SOFTWARE};
PORT - {SERVER_PORT};
ROOT - {DOCUMENT_ROOT};
REQUEST - {REQUEST_METHOD};
';
$contextr = [
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'],
    'SERVER_PORT' => $_SERVER['SERVER_PORT'],
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
];
$logger->log(\Psr\Log\LogLevel::INFO, $msg, $contextr);
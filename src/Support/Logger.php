<?php

namespace Jkdow\SimplyBook\Support;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Level;

class Logger
{
    protected static $logger;
    protected static $storageDir;

    public static function init($storageDir)
    {
        self::$logger = new MonologLogger('simplybook');
        self::$storageDir = $storageDir;

        if (php_sapi_name() === 'cli' || defined('WP_CLI')) {
            $output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
            // The fourth parameter (true) enables inline line breaks.
            $formatter = new LineFormatter($output, null, true, true);

            $stdoutHandler = new StreamHandler('php://stdout', Level::Debug);
            $stdoutHandler->setFormatter($formatter);
            self::$logger->pushHandler($stdoutHandler);

            $stderrHandler = new StreamHandler('php://stderr', Level::Error);
            $stderrHandler->setFormatter($formatter);
            self::$logger->pushHandler($stderrHandler);
        } else {
            if (!file_exists($storageDir . '/logs')) {
                mkdir($storageDir . '/logs', 0755, true);
            }
            $logFile = $storageDir . '/logs/plugin.log';
            self::$logger->pushHandler(new StreamHandler($logFile, Level::Debug));
            self::$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Level::Warning));
        }
    }

    public static function log($level, $message, array $context = [])
    {
        if (!self::$logger) {
            throw new \Exception("Logger is not initialized. Call Logger::init() first.");
        }
        $level = Level::fromName(strtoupper($level));
        self::$logger->log($level, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        self::log('debug', $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::log('info', $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::log('warning', $message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::log('error', $message, $context);
    }

    public static function dump($message, $object)
    {
        $obj_str = json_encode($object, JSON_PRETTY_PRINT);
        self::log('debug', $message, [$obj_str]);
    }

    public static function clear()
    {
        // delete log file
        $logFile = self::$storageDir . '/logs/plugin.log';
        if (file_exists($logFile) && $fp = fopen($logFile, 'r+')) {
            ftruncate($fp, 0);
            fclose($fp);
        }
    }

    public static function getLogs(int $limit = 200): array
    {
        if (!self::$storageDir) {
            throw new \Exception('Logger not initialized: missing storageDir');
        }
        $logFile = self::$storageDir . '/logs/plugin.log';
        if (!file_exists($logFile)) {
            return [];
        }
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($limit > 0 && count($lines) > $limit) {
            $lines = array_slice($lines, -$limit);
        }

        $entries = [];
        foreach ($lines as $line) {
            $entries[] = self::processLogLine($line);
        }
        return $entries;
    }

    protected static function processLogLine($line)
    {
        // Example line: [2025-04-24T04:23:07.665396+00:00] simplybook.INFO: Message here {"key":"value"}
        if (preg_match('/^\[(.+?)\]\s+(\S+)\.(\w+):\s+(.*)$/', $line, $m)) {
            list(, $datetime, $channel, $level, $rest) = $m;
            // Split context if JSON-like at end
            $context = '';
            $message = $rest;
            if (preg_match('/^(.*)\s+(\{.*\})$/', $rest, $m2)) {
                $message = $m2[1];
                $context = $m2[2];
            }
            return [
                'datetime' => $datetime,
                'channel'  => $channel,
                'level'    => strtoupper($level),
                'message'  => $message,
                'context'  => $context,
            ];
        } else {
            // fallback raw
            return [
                'datetime' => '',
                'channel'  => '',
                'level'    => 'DEBUG',
                'message'  => $line,
                'context'  => '',
            ];
        }
    }
}

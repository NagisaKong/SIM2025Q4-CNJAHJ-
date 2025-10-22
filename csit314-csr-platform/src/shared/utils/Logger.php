<?php
declare(strict_types=1);

namespace shared\utils;

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::writeLog('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::writeLog('ERROR', $message, $context);
    }

    private static function writeLog(string $level, string $message, array $context): void
    {
        $logDir = dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        $entry = sprintf(
            "[%s] %s %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
        file_put_contents($logDir . '/app.log', $entry, FILE_APPEND);
    }
}

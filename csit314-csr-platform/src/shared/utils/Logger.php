<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Utils;

final class Logger
{
    private string $logFile;

    public function __construct(?string $logFile = null)
    {
        $baseDir = __DIR__ . '/../../../storage/logs';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        $this->logFile = $logFile ?? $baseDir . '/application.log';
    }

    public function info(string $message, array $context = []): void
    {
        $this->writeLog('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->writeLog('ERROR', $message, $context);
    }

    private function writeLog(string $level, string $message, array $context): void
    {
        $line = sprintf(
            "[%s] %s %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context === [] ? '' : json_encode($context, JSON_THROW_ON_ERROR)
        );
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }
}

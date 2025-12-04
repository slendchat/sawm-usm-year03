<?php

namespace App\Core;

use Throwable;

/**
 * Records PHP warnings/exceptions/shutdown errors into storage/logs/error.log.
 */
class ErrorLogger
{
    /**
     * Log generic PHP error.
     *
     * @param string $level
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     */
    public static function recordError(string $level, string $message, string $file, int $line, array $context = []): void
    {
        self::write([
            'timestamp' => date('c'),
            'type'      => $level,
            'message'   => self::sanitize($message),
            'file'      => self::sanitize($file),
            'line'      => $line,
            'user_id'   => $_SESSION['user']['id'] ?? null,
            'role'      => $_SESSION['user']['role'] ?? (!empty($_SESSION['user']['is_admin']) ? 'admin' : 'guest'),
            'context'   => $context,
        ]);
    }

    /**
     * Log exceptions with stack.
     */
    public static function recordException(Throwable $e): void
    {
        self::write([
            'timestamp' => date('c'),
            'type'      => 'EXCEPTION',
            'class'     => get_class($e),
            'message'   => self::sanitize($e->getMessage()),
            'file'      => self::sanitize($e->getFile()),
            'line'      => $e->getLine(),
            'code'      => $e->getCode(),
            'user_id'   => $_SESSION['user']['id'] ?? null,
            'role'      => $_SESSION['user']['role'] ?? (!empty($_SESSION['user']['is_admin']) ? 'admin' : 'guest'),
            'trace'     => self::shortTrace($e),
        ]);
    }

    private static function write(array $payload): void
    {
        if (!defined('APP_ERROR_LOG_PATH')) {
            return;
        }

        $dir = dirname(APP_ERROR_LOG_PATH);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (!file_exists(APP_ERROR_LOG_PATH)) {
            @touch(APP_ERROR_LOG_PATH);
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return;
        }

        @file_put_contents(APP_ERROR_LOG_PATH, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private static function sanitize(string $value): string
    {
        return preg_replace('/[\\x00-\\x1F\\x7F]+/u', '', $value) ?? '';
    }

    /**
     * Return a short stack trace to avoid huge log lines.
     */
    private static function shortTrace(Throwable $e): array
    {
        $trace = $e->getTrace();
        $short = [];
        foreach ($trace as $frame) {
            $short[] = [
                'file' => isset($frame['file']) ? self::sanitize($frame['file']) : null,
                'line' => $frame['line'] ?? null,
                'function' => $frame['function'] ?? null,
            ];
            if (count($short) >= 10) {
                break;
            }
        }
        return $short;
    }
}

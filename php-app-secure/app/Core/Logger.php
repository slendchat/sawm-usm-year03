<?php

namespace App\Core;

/**
 * Lightweight file logger that records activity in a structured JSON format.
 *
 * Sensitive personal data (names, emails, plaintext payloads) must never be logged.
 * Only technical identifiers such as user_id, roles, and hashes are persisted.
 */
class Logger
{
    /**
     * Write an informational entry to the log file.
     *
     * @param string $action   Short machine-friendly action name (e.g., "login_success").
     * @param array  $context  Additional metadata (only IDs / hashes / technical fields).
     */
    public static function info(string $action, array $context = []): void
    {
        self::write('INFO', $action, $context);
    }

    /**
     * Internal writer that appends the log line to APP_LOG_PATH.
     */
    protected static function write(string $level, string $action, array $context): void
    {
        if (!defined('APP_LOG_PATH') || !self::ensureLogFile()) {
            return;
        }

        $user = self::userContext();
        $payload = [
            'timestamp' => date('c'),
            'level'     => $level,
            'user_id'   => $user['id'],
            'role'      => $user['role'],
            'ip'        => $user['ip'],
            'action'    => $action,
            'context'   => self::sanitizeContext($context),
        ];

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return;
        }

        file_put_contents(APP_LOG_PATH, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Resolve session user info without exposing personal data.
     */
    protected static function userContext(): array
    {
        $userId = null;
        $role   = 'guest';
        if (!empty($_SESSION['user']['id'])) {
            $userId = (int) $_SESSION['user']['id'];
            $role   = $_SESSION['user']['role'] ?? (!empty($_SESSION['user']['is_admin']) ? 'admin' : 'user');
        }

        return [
            'id'  => $userId,
            'role'=> $role,
            'ip'  => self::requestIp(),
        ];
    }

    /**
     * Clean context values to avoid log forging and personal data leakage.
     */
    protected static function sanitizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_string($value)) {
                $context[$key] = self::stripControlChars($value);
            } elseif (is_array($value)) {
                $context[$key] = self::sanitizeContext($value);
            }
        }

        return $context;
    }

    /**
     * Basic control-character removal.
     */
    protected static function stripControlChars(string $value): string
    {
        return preg_replace('/[\\x00-\\x1F\\x7F]+/u', '', $value) ?? '';
    }

    /**
     * Determine requester IP (best effort).
     */
    protected static function requestIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Make sure the log file exists and is writable for the web server user.
     */
    private static function ensureLogFile(): bool
    {
        $logDir = dirname(APP_LOG_PATH);
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0777, true)) {
                return false;
            }
        } elseif (!is_writable($logDir)) {
            @chmod($logDir, 0777);
        }

        if (!file_exists(APP_LOG_PATH)) {
            if (!touch(APP_LOG_PATH)) {
                return false;
            }
        }

        if (!is_writable(APP_LOG_PATH)) {
            @chmod(APP_LOG_PATH, 0666);
        }

        return is_writable(APP_LOG_PATH);
    }
}

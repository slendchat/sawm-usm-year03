<?php

namespace App\Core;

use Throwable;

/**
 * Global error/exception/shutdown handler that logs and shows friendly notices.
 */
class ErrorHandler
{
    public const FRIENDLY_MESSAGE = 'Oops, something went wrong. Please try again or go back to the main page.';

    /**
     * Register handlers.
     */
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            // Let PHP handle suppressed errors.
            return false;
        }

        ErrorLogger::recordError(self::levelName($severity), $message, $file, $line);
        self::flashFriendlyMessage();

        return true;
    }

    public static function handleException(Throwable $e): void
    {
        ErrorLogger::recordException($e);

        if ($e instanceof HttpException) {
            http_response_code($e->getStatusCode());
            $msg = $e->getUserMessage();
            self::flashFriendlyMessage($msg);
            self::redirectBackToApp($msg);
        } else {
            http_response_code(500);
            self::flashFriendlyMessage();
            self::redirectToSafePage();
        }
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            ErrorLogger::recordError('FATAL', $error['message'], $error['file'], $error['line']);
            self::flashFriendlyMessage();
            self::redirectToSafePage();
        }
    }

    private static function flashFriendlyMessage(?string $message = null): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION['global_error'] = $message ?? self::FRIENDLY_MESSAGE;
    }

    private static function redirectToSafePage(): void
    {
        if (!defined('APP_SAFE_PAGE_PATH')) {
            exit;
        }

        if (!headers_sent()) {
            header('Location: ' . APP_SAFE_PAGE_URI);
        } else {
            if (is_readable(APP_SAFE_PAGE_PATH)) {
                readfile(APP_SAFE_PAGE_PATH);
            } else {
                echo '<p>' . htmlspecialchars(self::FRIENDLY_MESSAGE) . '</p>';
            }
        }

        exit;
    }

    private static function redirectBackToApp(?string $message = null): void
    {
        if (!headers_sent()) {
            header('Location: /');
        } else {
            $text = $message ?? self::FRIENDLY_MESSAGE;
            echo '<p>' . htmlspecialchars($text) . '</p>';
            echo '<p><a href="/">Return to home</a></p>';
        }
        exit;
    }

    private static function levelName(int $severity): string
    {
        return match ($severity) {
            E_WARNING, E_USER_WARNING => 'WARNING',
            E_NOTICE, E_USER_NOTICE => 'NOTICE',
            default => 'ERROR',
        };
    }
}

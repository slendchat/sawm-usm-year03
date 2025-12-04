<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Controller that exposes an admin-only page for inspecting application logs.
 */
class LogController extends Controller
{
    private const MAX_LINES = 400;

    /**
     * Display the latest log entries (newest first).
     */
    public function index(): void
    {
        $this->ensureAdmin();

        $filters = [
            'level'   => strtoupper(trim($_GET['level'] ?? '')),
            'action'  => trim($_GET['action'] ?? ''),
            'user_id' => trim($_GET['user_id'] ?? ''),
        ];

        $entries = $this->readLogEntries($filters);

        $this->view('admin/logs', [
            'title'    => 'Application Activity Logs',
            'entries'  => $entries,
            'filters'  => $filters,
            'levels'   => ['INFO','WARN','ERROR'],
        ]);
    }

    /**
     * Display PHP error log entries.
     */
    public function errors(): void
    {
        $this->ensureAdmin();
        $entries = $this->readErrorLog();

        $this->view('admin/error_logs', [
            'title'   => 'Application Error Log',
            'entries' => $entries,
        ]);
    }

    private function ensureAdmin(): void
    {
        if (empty($_SESSION['user']['is_admin'])) {
            header('Location: /');
            exit;
        }
    }

    /**
     * Read the tail of the log file and decode JSON lines.
     *
     * @return array<int, array<string,mixed>>
     */
    private function readLogEntries(array $filters = []): array
    {
        if (!defined('APP_LOG_PATH') || !file_exists(APP_LOG_PATH)) {
            return [];
        }

        $lines = @file(APP_LOG_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $slice = array_slice($lines, -self::MAX_LINES);
        $entries = [];
        foreach ($slice as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }

        $entries = array_reverse($entries);

        if ($filters) {
            $entries = array_values(array_filter($entries, function ($entry) use ($filters) {
                if ($filters['level'] && strtoupper($entry['level'] ?? '') !== $filters['level']) {
                    return false;
                }
                if ($filters['user_id'] !== '' && (string)($entry['user_id'] ?? '') !== $filters['user_id']) {
                    return false;
                }
                if ($filters['action'] !== '' && stripos($entry['action'] ?? '', $filters['action']) === false) {
                    return false;
                }
                return true;
            }));
        }

        return array_slice($entries, 0, self::MAX_LINES);
    }

    private function readErrorLog(): array
    {
        if (!defined('APP_ERROR_LOG_PATH') || !file_exists(APP_ERROR_LOG_PATH)) {
            return [];
        }
        $lines = @file(APP_ERROR_LOG_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }
        $slice = array_slice($lines, -self::MAX_LINES);
        $entries = [];
        foreach ($slice as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }
        return array_reverse($entries);
    }
}

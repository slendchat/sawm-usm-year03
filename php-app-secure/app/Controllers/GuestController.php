<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

/**
 * Guestbook controller: shows guestbook, handles submissions, and provides unsafe demo.
 */
class GuestController extends Controller
{
    /**
     * Display the guestbook with safe output.
     */
    public function index(): void
    {
        global $db;

        $stmt = $db->query('SELECT id, user, text_message, e_mail, data_time_message FROM guest ORDER BY data_time_message DESC');
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $errors  = $_SESSION['errors'] ?? [];
        $old     = $_SESSION['old'] ?? [];
        $success = $_SESSION['success'] ?? null;

        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('guest/index', [
            'title'    => 'Guest Book',
            'messages' => $messages,
            'errors'   => $errors,
            'old'      => $old,
            'success'  => $success,
        ]);
    }

    /**
     * Store a new guestbook message with server-side validation.
     */
    public function store(): void
    {
        $user  = trim($_POST['user'] ?? '');
        $email = trim($_POST['e_mail'] ?? '');
        $text  = trim($_POST['text_message'] ?? '');

        $errors = [];
        $warnings = [];

        if ($user === '' || $email === '' || $text === '') {
            $errors[] = 'Все поля обязательны к заполнению.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный адрес электронной почты.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['user' => $user, 'e_mail' => $email, 'text_message' => $text];
            header('Location: /guestbook');
            exit;
        }

        // Экранируем потенциально опасные фрагменты, но не блокируем запись
        [$userSafe, $hasUserXss] = $this->sanitizeInput($user);
        [$emailSafe, $hasEmailXss] = $this->sanitizeInput($email);
        [$textSafe, $hasTextXss] = $this->sanitizeInput($text);

        if ($hasUserXss) {
            $warnings[] = 'Имя содержит потенциальный XSS — текст экранирован.';
        }
        if ($hasEmailXss) {
            $warnings[] = 'E-mail содержит потенциальный XSS — текст экранирован.';
        }
        if ($hasTextXss) {
            $warnings[] = 'Сообщение содержит потенциальный XSS — текст экранирован.';
        }

        global $db;
        $stmt = $db->prepare('INSERT INTO guest (user, text_message, e_mail) VALUES (?, ?, ?)');
        $stmt->execute([$userSafe, $textSafe, $emailSafe]);

        Logger::info('guest_entry_created', [
            'email_hash'        => $this->anonymizeIdentifier($email),
            'has_suspected_xss' => !empty($warnings),
        ]);

        $notice = 'Сообщение добавлено.';
        if ($warnings) {
            $notice .= ' ' . implode(' ', $warnings);
        }

        $_SESSION['success'] = $notice;
        header('Location: /guestbook');
        exit;
    }

    /**
     * Show guestbook entries without escaping to demonstrate XSS.
     */
    public function unsafe(): void
    {
        global $db;
        $stmt = $db->query('SELECT id, user, text_message, e_mail, data_time_message FROM guest ORDER BY data_time_message DESC');
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('guest/unsafe', [
            'title'    => 'Guest Book (XSS demo)',
            'messages' => $messages,
        ]);
    }

    /**
     * Detect simple XSS patterns in the provided value.
     */
    private function containsXss(string $value): bool
    {
        $patterns = [
            '/<\s*script/i',
            '/javascript:/i',
            '/onerror\s*=|onload\s*=/i',
            '/<\s*img[^>]*src/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Escape potentially dangerous content and report if XSS patterns were found.
     *
     * @return array{string,bool} Tuple: [sanitizedValue, hadXss]
     */
    private function sanitizeInput(string $value): array
    {
        $hadXss = $this->containsXss($value);
        if ($hadXss) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return [$value, $hadXss];
    }

    private function anonymizeIdentifier(string $value): string
    {
        return $value === '' ? '' : hash('sha256', $value);
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

/**
 * Unsafe admin controller left intentionally without auth/session checks to
 * demonstrate how easy it is to access privileged functionality via a direct URL.
 */
class AdminUnsafeController extends Controller
{
    /**
     * Display the insecure admin creation form with no access control.
     *
     * @return void
     */
    public function showCreateForm()
    {
        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old']    ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('admin/create_user_insecure', compact('errors', 'old'));
    }

    /**
     * Process the insecure admin creation form, allowing anyone to create admin accounts.
     *
     * @return void
     */
    public function create()
    {
        $email = trim($_POST['email'] ?? '');
        $p1    = $_POST['password']  ?? '';
        $p2    = $_POST['password2'] ?? '';

        $errors = [];

        if (!$email || !$p1 || !$p2) {
            $errors[] = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif ($p1 !== $p2) {
            $errors[] = 'Passwords don’t match.';
        } elseif (strlen($p1) < 6) {
            $errors[] = 'Password must be at least 6 chars.';
        }

        if ($errors) {
            Logger::info('admin_create_failed_unsafe', ['reason' => 'validation']);
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = ['email' => $email];
            header('Location: /admin/unsafe/create');
            exit;
        }

        global $db;
        $hash = password_hash($p1, PASSWORD_BCRYPT);
        try {
            $stmt = $db->prepare("
                INSERT INTO users (email, password_hash, is_admin, role)
                VALUES (?, ?, 1, 'admin')
            ");
            $stmt->execute([$email, $hash]);
            $newAdminId = (int) $db->lastInsertId();
            Logger::info('admin_created_unsafe', ['new_admin_id' => $newAdminId]);
        } catch (\PDOException $e) {
            Logger::info('admin_create_failed_unsafe_duplicate', [
                'email_hash' => $this->anonymizeIdentifier($email),
            ]);
            $_SESSION['errors'] = ['User already exists.'];
            $_SESSION['old']    = ['email' => $email];
            header('Location: /admin/unsafe/create');
            exit;
        }

        $_SESSION['success'] = 'Admin account created (unsafe mode): ' . htmlspecialchars($email);
        header('Location: /admin/unsafe/create');
        exit;
    }

    private function anonymizeIdentifier(string $value): string
    {
        return $value === '' ? '' : hash('sha256', $value);
    }
}

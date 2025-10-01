<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Controller responsible for administrator user management.
 */
class AdminController extends Controller
{
    /**
     * Ensure the current user has admin privileges.
     *
     * Redirects to the homepage if the user is not an admin.
     *
     * @return void
     */
    private function ensureAdmin()
    {
        if (empty($_SESSION['user']['is_admin'])) {
            header('Location: /'); exit;
        }
    }

    /**
     * Show the form for creating a new admin user.
     *
     * Pulls any validation errors and previous input from the session,
     * clears them, and renders the creation view.
     *
     * @return void
     */
    public function showCreateForm()
    {
        $this->ensureAdmin();

        // вытащим из сессии старые данные/ошибки, если были
        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old']    ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('admin/create_user', compact('errors','old'));
    }

    /**
     * Process the submission of the admin creation form.
     *
     * - Validates email format and password fields
     * - On validation failure, stores errors and old input in session and redirects back
     * - On success, hashes the password, inserts a new admin user into the database,
     *   sets a success message in session, and redirects back to the form
     *
     * @return void
     */
    public function create()
    {
        $this->ensureAdmin();

        // читаем из POST
        $email = trim($_POST['email'] ?? '');
        $p1    = $_POST['password'] ?? '';
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
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = ['email'=>$email];
            header('Location: /admin/users/create'); exit;
        }

        global $db;
        $hash = password_hash($p1, PASSWORD_BCRYPT);
        try {
            $stmt = $db->prepare("
                INSERT INTO users (email, password_hash, is_admin)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$email, $hash]);
        } catch (\PDOException $e) {
            $_SESSION['errors'] = ['User already exists.'];
            $_SESSION['old']    = ['email'=>$email];
            header('Location: /admin/users/create'); exit;
        }

        $_SESSION['success'] = 'Admin account created: ' . htmlspecialchars($email);
        header('Location: /admin/users/create'); exit;
    }
}

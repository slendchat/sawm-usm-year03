<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Logger;

/**
 * Controller responsible for user authentication and registration.
 */
class AuthController extends Controller
{
    /**
     * Display the login form.
     *
     * @return void
     *   Renders the 'auth/login' view.
     */
    public function showLoginForm()
    {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $this->view('auth/login', compact('errors'));
    }

    /**
     * Handle login form submission.
     *
     * Reads 'email' and 'password' from $_POST, validates presence,
     * looks up the user in the database, verifies the password,
     * and establishes the session on success.
     * On failure, stores error messages in session and redirects back to login.
     *
     * @return void
     *   Redirects to '/login' on validation or authentication failure,
     *   or to '/' on successful login.
     */
    public function login()
    {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (!$email || !$pass) {
            Logger::info('login_failed', ['reason' => 'missing_fields']);
            $_SESSION['errors'] = ['Fill every field.'];
            header('Location: /login'); exit;
        }


        global $db;
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($pass, $user['password_hash'])) {
            Logger::info('login_failed', [
                'reason'      => 'invalid_credentials',
                'email_hash'  => $this->anonymize($email),
            ]);
            $_SESSION['errors'] = ['The wrong login or password.'];
            header('Location: /login'); exit;
        }


        $role = $user['role'] ?? null;
        $_SESSION['user'] = [
            'id'         => $user['id'],
            'email'      => $user['email'],
            'role'       => $role,
            'is_admin'   => $role === 'admin',
            'is_manager' => $role === 'manager',
        ];
        $_SESSION['success']  = 'You logged in as '.$user['email'];
        Logger::info('login_success', ['authenticated_user_id' => (int) $user['id']]);
        header('Location: /'); exit;
    }

    /**
     * Display the registration form.
     *
     * @return void
     *   Renders the 'auth/register' view.
     */
    public function showRegisterForm()
    {
        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old']    ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('auth/register', compact('errors','old'));
    }

    /**
     * Handle registration form submission.
     *
     * Reads 'email', 'password', and 'password2' from $_POST,
     * validates required fields, password match, and length,
     * hashes the password, and inserts a new user record.
     * On validation or insertion failure, stores errors in session and redirects back.
     *
     * @return void
     *   Redirects to '/register' on validation failure,
     *   or to '/login' on successful registration.
     */
    public function register()
    {
        $email = trim($_POST['email'] ?? '');
        $pass1 = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        $errors = [];
        if (!$email || !$pass1 || !$pass2) {
            $errors[] = 'All fields requiered.';
        } elseif ($pass1 !== $pass2) {
            $errors[] = 'Passwords do not match.';
        } elseif (strlen($pass1)<6) {
            $errors[] = 'Password length at least 6 characters.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = ['email' => $email];
            Logger::info('registration_failed', ['reason' => 'validation']);
            header('Location: /register'); exit;
        }

        global $db;
        $hash = password_hash($pass1, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (email,password_hash) VALUES (?,?)");
        try {
            $stmt->execute([$email,$hash]);
            $newUserId = (int) $db->lastInsertId();
            Logger::info('registration_success', ['new_user_id' => $newUserId]);
        } catch(\PDOException $e) {
            Logger::info('registration_failed_duplicate', [
                'email_hash' => $this->anonymize($email),
            ]);
            $_SESSION['errors'] = ['User already exists.'];
            $_SESSION['old']    = ['email' => $email];
            header('Location: /register'); exit;
        }

        $_SESSION['success'] = 'Registration successful. Please log in.';
        unset($_SESSION['old']);
        header('Location: /login'); exit;
        exit;
    }

    /**
     * Log out the current user.
     *
     * Clears all session data and redirects to homepage.
     *
     * @return void
     */
    public function logout()
    {
        Logger::info('logout', []);
        session_unset();
        session_destroy();
        header('Location: /'); exit;
    }

    /**
     * Return SHA-256 hash to anonymize identifiers in logs.
     */
    private function anonymize(string $value): string
    {
        return $value === '' ? '' : hash('sha256', $value);
    }
}

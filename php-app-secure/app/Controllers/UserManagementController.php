<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

/**
 * Controller that exposes user listing/editing/deleting for admins and managers.
 */
class UserManagementController extends Controller
{
    /**
     * Restrict access to admins and managers only.
     */
    private function ensureStaff(): void
    {
        $role = $_SESSION['user']['role'] ?? null;
        if (empty($_SESSION['user']) || !in_array($role, ['admin', 'manager'], true)) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Determine whether the current staff user may manage the provided account.
     */
    private function canManageTarget(array $user): bool
    {
        $currentRole = $_SESSION['user']['role'] ?? null;
        if ($currentRole === 'admin') {
            return true;
        }
        // Managers cannot modify administrator accounts
        if (($user['role'] ?? null) === 'admin') {
            return false;
        }
        return true;
    }

    /**
     * List all users with basic profile data.
     */
    public function index(): void
    {
        $this->ensureStaff();
        global $db;
        $stmt = $db->query("SELECT id, email, role, created_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $this->view('users/index', [
            'title' => 'User Directory',
            'users' => $users,
            'currentRole' => $_SESSION['user']['role'] ?? null,
            'errors' => $errors,
        ]);
    }

    /**
     * Show edit form for a specific user.
     */
    public function editForm(): void
    {
        $this->ensureStaff();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: /users');
            exit;
        }

        global $db;
        $stmt = $db->prepare("SELECT id, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user || !$this->canManageTarget($user)) {
            header('Location: /users');
            exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old']    ?? $user;
        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('users/edit', [
            'title' => 'Edit User #' . $user['id'],
            'user'  => $user,
            'old'   => $old,
            'errors'=> $errors,
        ]);
    }

    /**
     * Update user information (currently email only).
     */
    public function update(): void
    {
        $this->ensureStaff();
        $id = (int)($_POST['id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        if (!$id || $email === '') {
            $_SESSION['errors'] = ['Email is required.'];
            header('Location: /users/edit?id='.$id);
            exit;
        }

        global $db;
        $stmt = $db->prepare("SELECT id, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user || !$this->canManageTarget($user)) {
            header('Location: /users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Invalid email format.'];
            $_SESSION['old'] = ['id'=>$id, 'email'=>$email];
            header('Location: /users/edit?id='.$id);
            exit;
        }

        try {
            $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $id]);
        } catch (\PDOException $e) {
            $_SESSION['errors'] = ['This email is already taken.'];
            $_SESSION['old']    = ['id'=>$id, 'email'=>$email];
            header('Location: /users/edit?id='.$id);
            exit;
        }

        $_SESSION['success'] = 'User updated.';
        Logger::info('user_directory_update', ['target_user_id' => $id]);
        header('Location: /users');
        exit;
    }

    /**
     * Delete a user account.
     */
    public function delete(): void
    {
        $this->ensureStaff();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: /users');
            exit;
        }

        if ($id === ($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['errors'] = ['You cannot delete your own account.'];
            header('Location: /users');
            exit;
        }

        global $db;
        $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user || !$this->canManageTarget($user)) {
            header('Location: /users');
            exit;
        }

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount()) {
            Logger::info('user_directory_delete', ['target_user_id' => $id]);
        }
        $_SESSION['success'] = "User #{$id} deleted.";
        header('Location: /users');
        exit;
    }
}

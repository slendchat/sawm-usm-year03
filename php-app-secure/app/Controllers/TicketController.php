<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\HttpException;

/**
 * Controller for managing tickets: listing, viewing, creating, editing, deleting, and status changes.
 */
class TicketController extends Controller
{

    /**
     * Display a list of tickets with optional filters.
     *
     * Reads filter parameters from the query string:
     *   - q: search keyword for title
     *   - category: ticket category
     *   - priority: ticket priority
     *
     * Access control:
     *   - Admin sees all statuses
     *   - Logged-in user sees 'Open' and 'Closed'
     *   - Guest sees only 'Open'
     *
     * @return void
     */
    public function index()
    {
        global $db;

        $isLoggedIn = !empty($_SESSION['user']);
        $canManage  = $this->userCanManageTickets();

        // 1) Read GET filters
        $q        = trim($_GET['q']       ?? '');
        $category =       $_GET['category'] ?? '';
        $priority =       $_GET['priority'] ?? '';

        // 2) Build WHERE parts
        $whereParts = [];
        $params     = [];

    
        if (!$canManage) {
            if ($isLoggedIn) {
            $whereParts[] = "status IN ('Open','Closed')";
            } else {
                $whereParts[] = "status = 'Open'";
            }
        }

        if ($q !== '') {
            $whereParts[] = 'title LIKE ?';
            $params[]     = "%{$q}%";
        }

        if ($category !== '') {
            $whereParts[] = 'category = ?';
            $params[]     = $category;
        }

        if ($priority !== '') {
            $whereParts[] = 'priority = ?';
            $params[]     = $priority;
        }

        $sql = "
            SELECT id, title, category, status, created_at
            FROM tickets
        ";
        if ($whereParts) {
            $sql .= ' WHERE ' . implode(' AND ', $whereParts);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt    = $db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('tickets/index', [
            'title'   => $canManage ? 'All Tickets'
                      : ($isLoggedIn ? 'Active Tickets' : 'Open Tickets'),
            'tickets' => $tickets,
            'canManageTickets' => $canManage,
            'filters' => compact('q','category','priority'),
        ]);
    }

    /**
     * Display details of a single ticket.
     *
     * Reads 'id' from the query string, redirects if missing or not found.
     * Pending tickets are restricted to admins.
     *
     * @return void
     */
    public function show()
    {
        global $db;
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) return header('Location:/tickets');

        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$ticket) return $this->abort404();

        if ($ticket['status']==='Pending' && !$this->userCanManageTickets()) {
            return header('Location:/tickets');
        }

        $this->view('tickets/show', [
            'title'=>'Ticket #'.$ticket['id'],
            'ticket'=>$ticket,
            'canManageTickets'=>$this->userCanManageTickets()
        ]);
    }
    
    /**
     * Show the form for creating a new ticket.
     *
     * Redirects to login page if the user is not authenticated.
     *
     * @return void
     */
    public function createForm()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login'); exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old']    ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('tickets/create', [
            'title'  => 'Create Ticket',
            'errors' => $errors,
            'old'    => $old,
        ]);
    }

    /**
     * Handle the submission of a new ticket.
     *
     * Reads input from POST:
     *   - title (string)
     *   - description (string)
     *   - category (string)
     *   - priority (string)
     *   - due_date (YYYY-MM-DD)
     *   - is_urgent (checkbox)
     *
     * Validates inputs and redirects back with errors on failure.
     * Inserts ticket and redirects to ticket list on success.
     *
     * @return void
     */
    public function create()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login'); exit;
        }

        $old = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category'] ?? '',
            'priority'    => $_POST['priority'] ?? '',
            'due_date'    => $_POST['due_date'] ?? '',
            'is_urgent'   => isset($_POST['is_urgent']) ? 1 : 0,
        ];

        $errors = [];
        if ($old['title']==='') {
            $errors[] = 'Title is required.';
        } elseif (mb_strlen($old['title']) > 255) {
            $errors[] = 'Title must be ≤ 255 chars.';
        }

        if ($old['description']==='') {
            $errors[] = 'Description is required.';
        }

        $allowedCats = ['Server','Administration','Network','Other'];
        if (!in_array($old['category'], $allowedCats, true)) {
            $errors[] = 'Invalid category.';
        }

        $allowedPrio = ['Low','Medium','High'];
        if (!in_array($old['priority'], $allowedPrio, true)) {
            $errors[] = 'Invalid priority.';
        }

        if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $old['due_date'])
            || !strtotime($old['due_date'])
        ) {
            $errors[] = 'Due date is invalid.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $old;
            header('Location: /ticket/create');
            exit;
        }

        global $db;
        $stmt = $db->prepare("
            INSERT INTO tickets
            (user_id, title, description, category, priority, due_date, is_urgent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user']['id'],
            $old['title'],
            $old['description'],
            $old['category'],
            $old['priority'],
            $old['due_date'],
            $old['is_urgent'],
        ]);

        $ticketId = (int) $db->lastInsertId();
        Logger::info('ticket_created', [
            'ticket_id' => $ticketId,
            'category'  => $old['category'],
            'priority'  => $old['priority'],
            'is_urgent' => (int) $old['is_urgent'],
        ]);

        $_SESSION['success'] = 'Ticket created successfully.';
        header('Location: /tickets');
        exit;
    }

    /**
     * Show the form for editing an existing ticket.
     *
     * Access restricted to admins. Reads 'id' from the query string.
     *
     * @return void
     */
    public function editForm(){
        if (!$this->userCanManageTickets()) {
            header('Location:/tickets'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location:/tickets'); exit; }

        global $db;
        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$ticket) { $this->abort404(); }

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old']    ?? $ticket;
        unset($_SESSION['errors'], $_SESSION['old']);

        $this->view('tickets/edit', compact('errors','old'));
    }

    /**
     * Handle the submission of ticket edits.
     *
     * Reads input from POST including id, title, description, category, priority, due_date,
     * is_urgent, and status. Validates inputs and redirects back with errors on failure.
     * Updates ticket and redirects to ticket detail on success.
     *
     * @return void
     */
    public function edit(){
        if (!$this->userCanManageTickets()) {
            header('Location:/tickets'); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location:/tickets'); exit; }

        global $db;
        $existingStmt = $db->prepare("
            SELECT title, description, category, priority, due_date, is_urgent, status
            FROM tickets WHERE id = ?
        ");
        $existingStmt->execute([$id]);
        $current = $existingStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$current) {
            header('Location:/tickets'); exit;
        }

        $old = [
          'id'          => $id,
          'title'       => trim($_POST['title'] ?? ''),
          'description' => trim($_POST['description'] ?? ''),
          'category'    => $_POST['category'] ?? '',
          'priority'    => $_POST['priority'] ?? '',
          'due_date'    => $_POST['due_date'] ?? '',
          'is_urgent'   => isset($_POST['is_urgent']) ? 1 : 0,
          'status'      => $_POST['status'] ?? 'Pending',
        ];

        $errors = [];
        if ($old['title']==='') {
          $errors[] = 'Title is required.';
        }

        if (!in_array($old['status'], ['Pending','Open','Closed'], true)) {
          $errors[] = 'Invalid status.';
        }

        if ($errors) {
          $_SESSION['errors'] = $errors;
          $_SESSION['old']    = $old;
          header('Location: /ticket/edit?id=' . $id);
          exit;
        }

        $stmt = $db->prepare("
          UPDATE tickets SET
            title       = ?,
            description = ?,
            category    = ?,
            priority    = ?,
            due_date    = ?,
            is_urgent   = ?,
            status      = ?
          WHERE id = ?
        ");
        $stmt->execute([
          $old['title'], $old['description'], $old['category'],
          $old['priority'], $old['due_date'], $old['is_urgent'],
          $old['status'], $id
        ]);

        $tracked = ['title','description','category','priority','due_date','is_urgent','status'];
        $changedFields = [];
        foreach ($tracked as $field) {
            $prev = $current[$field] ?? null;
            $newValue = $old[$field] ?? null;
            if ($field === 'is_urgent') {
                $prev = (int) $prev;
                $newValue = (int) $newValue;
            }
            if ((string) $prev !== (string) $newValue) {
                $changedFields[] = $field;
            }
        }

        Logger::info('ticket_updated', [
            'ticket_id'      => $id,
            'changed_fields' => $changedFields,
        ]);

        $_SESSION['success'] = 'Ticket updated.';
        header('Location: /ticket?id=' . $id);
        exit;
    }

    /**
     * Delete a ticket by ID.
     *
     * Access restricted to admins. Reads 'id' from the
     * hidden field
     * 
     * @return void
     */
    public function delete()
    {
        if (!$this->userCanManageTickets()) return header('Location:/tickets');
        $id = (int)($_GET['id']??0);
        if ($id) {
          global $db;
          $stmt = $db->prepare("DELETE FROM tickets WHERE id=?");
          $stmt->execute([$id]);
          if ($stmt->rowCount()) {
            Logger::info('ticket_deleted', ['ticket_id' => $id]);
          }
        }
        $_SESSION['success']='Ticket deleted.';
        header('Location:/tickets');
    }

    /**
     * Changes ticket status.
     *
     * Access restricted to admins. Reads 'id' from the
     * hidden field.
     * 
     * @return void
     */
    public function changeStatus()
    {
        if (!$this->userCanManageTickets()) return header('Location:/tickets');
        $id = (int)($_POST['id']??0);
        $status = $_POST['status']??'Open';
        if ($id && in_array($status,['Open','Closed','Pending'],true)) {
          global $db;
          $stmt = $db->prepare("UPDATE tickets SET status=? WHERE id=?");
          $stmt->execute([$status,$id]);
          if ($stmt->rowCount()) {
            Logger::info('ticket_status_changed', [
                'ticket_id' => $id,
                'status'    => $status,
            ]);
          }
          $_SESSION['success']="Status changed to $status.";
        }
        header('Location:/ticket?id='.$id);
    }

    protected function abort404()
    {
        throw new HttpException(404, 'Ticket not found', 'Запрошенный ресурс не найден.');
    }

    /**
     * Determine whether the current user can manage tickets (admin or manager role).
     */
    protected function userCanManageTickets(): bool
    {
        if (empty($_SESSION['user'])) {
            return false;
        }
        $role = $_SESSION['user']['role'] ?? null;
        if ($role === null) {
            return !empty($_SESSION['user']['is_admin']);
        }
        return in_array($role, ['admin','manager'], true);
    }
    
}

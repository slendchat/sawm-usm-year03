<?php
/** @var array $users */
/** @var string|null $currentRole */
?>
<div class="tui-window">
  <fieldset class="tui-fieldset">
    <legend>User Accounts</legend>
    <p>
      Здесь администратор и менеджер могут просматривать учетные записи и управлять ими.
      Простые пользователи не имеют доступа к данному разделу.
    </p>
  </fieldset>
</div>

<div class="tui-window">
  <fieldset class="tui-fieldset">
    <legend>Directory</legend>
    <?php if (empty($users)): ?>
      <p>No users yet.</p>
    <?php else: ?>
      <table class="tui-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php
              $roleLabel = $u['role'] ?: 'user';
              $isAdminRow = $u['role'] === 'admin';
              $canEdit = $currentRole === 'admin' || !$isAdminRow;
            ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($roleLabel) ?></td>
              <td><?= htmlspecialchars($u['created_at']) ?></td>
              <td>
                <?php if ($canEdit): ?>
                  <a class="tui-button mini" href="/users/edit?id=<?= $u['id'] ?>">Edit</a>
                  <?php if ($u['id'] !== ($_SESSION['user']['id'] ?? 0)): ?>
                    <form action="/users/delete" method="post" style="display:inline;" onsubmit="return confirm('Delete user #<?= $u['id'] ?>?');">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
                      <button class="tui-button mini red-168" type="submit">Delete</button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="yellow-255-text">Admin account (read-only)</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </fieldset>
</div>

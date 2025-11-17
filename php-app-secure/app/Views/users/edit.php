<?php
/** @var array $user */
/** @var array $old */
?>
<div class="tui-window">
  <fieldset class="tui-fieldset">
    <legend>Edit User #<?= htmlspecialchars($user['id']) ?></legend>
    <form action="/users/edit" method="post">
      <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
      <div class="form-row">
        <label>Email.............:</label>
        <input
          class="tui-input"
          type="email"
          name="email"
          value="<?= htmlspecialchars($old['email'] ?? $user['email']) ?>"
          required
        >
      </div>
      <div class="form-row">
        <label>Role (readonly)..:</label>
        <input
          class="tui-input"
          type="text"
          value="<?= htmlspecialchars($user['role'] ?: 'user') ?>"
          disabled
        >
      </div>
      <div class="center">
        <button class="tui-button" type="submit">Save</button>
        <a href="/users" class="tui-button blue-168 white-255-text">Back</a>
      </div>
    </form>
  </fieldset>
</div>

<!-- <?php if (!empty($_SESSION['success'])): ?>
  <div class="tui-window green-168" style="margin-bottom: 2rem;">
    <fieldset class="tui-fieldset">
      <legend class="yellow-255-text">Success</legend>
    <?= htmlspecialchars($_SESSION['success']) ?>
    </fieldset>
    </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="tui-window red-168" style="margin-bottom: 2rem;">
    <fieldset class="tui-fieldset">
      <legend class="yellow-255-text">Alert</legend>
      <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </fieldset>
  </div>
<?php endif; ?> 

/////////// перенесено в header.php после <main>
-->

<div class="tui-window">
  <fieldset class="tui-fieldset tui-border-solid">
    <legend>Create New Admin</legend>
<form action="/admin/users/create" method="post">
  <div class="form-row">
  <label>Email.............:</label>
    <input class="tui-input" type="email" name="email"
           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
           required>
  </div>
  
  <div class="form-row">
  <label>Password..........:</label>
    <input class="tui-input" type="password" name="password" required minlength="6">
    </div>
  
    <div class="form-row">
  <label>Repeat Password...:</label>
    <input class="tui-input" type="password" name="password2" required minlength="6">
    </div>

<div class="center">
<button class="tui-button" type="submit">Create Admin</button>
</div>
  
</form>
  </fieldset>
</div>

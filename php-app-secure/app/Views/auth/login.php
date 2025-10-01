<!-- <?php if (!empty($_SESSION['success'])): ?>
  <div class="success">
    <?= htmlspecialchars($_SESSION['success']) ?>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['errors'])): ?>
  <div class="errors">
    <?php foreach ($_SESSION['errors'] as $err): ?>
      <p><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
    <?php unset($_SESSION['errors']); ?>
  </div>
<?php endif; ?> 

//// есть в header.php
-->

<div class="login-form">
  <div class="tui-window">
  <form action="/login" method="post">
    <fieldset class="tui-fieldset tui-border-solid">
      <legend>Enter account</legend>
      <div class="form-row">
      <label>Email...............:</label><input class="tui-input" placeholder="johndoe@example.com" type="text" name="email" required>
      </div>
      <div class="form-row">
      <label>Password............:</label><input class="tui-input" placeholder="!ExamplePass123" type="password" name="password" required>
      </div>
      <div class="center"><button class="tui-button" type="submit">Log in</button></div>
  </fieldset>
</form>
  </div>
  <div class="login-advice center">
  <p><span class="orange-168">Not registered?</span> <a href="/register"><button class="tui-button blue-255 green-168-text">Sign up</button></a></p>
  </div>
</div>
<script src="/../../js/auth-sql-guard.js"></script>
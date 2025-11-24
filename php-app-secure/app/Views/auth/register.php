<!-- <?php if(!empty($_SESSION['errors'])): ?>
  <div class="errors">
    <?php foreach($_SESSION['errors'] as $err): ?>
      <p><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
    <?php unset($_SESSION['errors']); ?>
  </div>
<?php endif; ?> 

////// тоже должно быть уже в header.php
-->

<div class="register-form">
  <div class="tui-window">
    <form action="/register" method="post">
      <fieldset class="tui-fieldset tui-border-solid">
        <legend>Register account</legend>
      <div class="form-row">
        <label>Email...............:</label> <input class="tui-input" placeholder="johndoe@example.com" type="text" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label>Password............:</label> <input class="tui-input" placeholder="!ExamplePass123" type="password" name="password" required>
      </div>

      <div class="form-row">
        <label>Re-enter password...:</label> <input class="tui-input" placeholder="!ExamplePass123" type="password" name="password2" required>
      </div>

    <div class="center">
      <button class="tui-button" type="submit">Sign up</button>
    </div>
    </fieldset>
</form>
  </div>
  <div class="login-advice center">
  <p><span class="orange-168">Already registered?</span> <a href="/login"><button class="tui-button blue-255 green-168-text">Log in</button></a></p>
  </div>
</div>

<script src="/../../js/auth-sql-guard.js"></script>

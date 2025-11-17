<div class="tui-window red-168" style="margin-bottom: 2rem;">
  <fieldset class="tui-fieldset tui-border-dashed">
    <legend>Unsafe Demo</legend>
    <p>
      This page mimics the admin panel but intentionally has <strong>no authentication</strong>.
      Anyone who knows the URL <code>/admin/unsafe/create</code> can open it and create a new
      administrator account. Use it only to demonstrate the vulnerability from Lab 05 &mdash;
      never leave it enabled in production!
    </p>
  </fieldset>
</div>

<div class="tui-window">
  <fieldset class="tui-fieldset tui-border-solid">
    <legend>Unsafe Admin Creation</legend>
    <form action="/admin/unsafe/create" method="post">
      <div class="form-row">
        <label>Email.............:</label>
        <input
          class="tui-input"
          type="email"
          name="email"
          value="<?= htmlspecialchars($old['email'] ?? '') ?>"
          required
        >
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
        <button class="tui-button red-255" type="submit">Create Admin (Unsafe)</button>
      </div>
    </form>
  </fieldset>
</div>

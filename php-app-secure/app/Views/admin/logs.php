<?php
/** @var array $entries */
/** @var array $filters */
/** @var array $levels */
?>
<div class="tui-window full-width">
  <fieldset class="tui-fieldset">
    <legend>Activity Log</legend>
    <p>
      This log intentionally stores only technical identifiers (user IDs, roles, hashes) to
      avoid collecting personal data such as names or emails.
    </p>
    <form class="log-filter-form" action="/admin/logs" method="get">
      <label>
        Level:
        <select class="tui-input" name="level">
          <option value="">Any</option>
          <?php foreach ($levels as $levelOption): ?>
            <option value="<?= $levelOption ?>"
              <?= ($filters['level'] ?? '') === $levelOption ? 'selected' : '' ?>>
              <?= $levelOption ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        User ID:
        <input class="tui-input" type="text" name="user_id" value="<?= htmlspecialchars($filters['user_id'] ?? '') ?>" placeholder="e.g. 1">
      </label>
      <label>
        Action contains:
        <input class="tui-input" type="text" name="action" value="<?= htmlspecialchars($filters['action'] ?? '') ?>" placeholder="login">
      </label>
      <div class="log-filter-actions">
        <button class="tui-button" type="submit">Apply</button>
        <?php if (!empty(array_filter($filters))): ?>
          <a class="tui-button blue-168 white-255-text" href="/admin/logs">Reset</a>
        <?php endif; ?>
      </div>
    </form>
  </fieldset>
</div>

<div class="tui-window full-width">
  <fieldset class="tui-fieldset">
    <legend>Latest Entries</legend>
    <?php if (empty($entries)): ?>
      <p>No log entries match your filters.</p>
    <?php else: ?>
      <p class="log-results-hint">Showing <?= count($entries) ?> entr<?= count($entries) === 1 ? 'y' : 'ies' ?> (newest first).</p>
      <div class="log-card-list">
        <?php foreach ($entries as $entry): ?>
          <article class="log-card">
            <header>
              <div>
                <div class="log-card-action"><?= htmlspecialchars($entry['action'] ?? '') ?></div>
                <small><?= htmlspecialchars($entry['timestamp'] ?? '') ?></small>
              </div>
              <span class="log-level-badge log-level-<?= strtolower($entry['level'] ?? 'info') ?>">
                <?= htmlspecialchars($entry['level'] ?? 'INFO') ?>
              </span>
            </header>
            <dl>
              <div>
                <dt>User ID</dt>
                <dd><?= htmlspecialchars((string)($entry['user_id'] ?? '—')) ?></dd>
              </div>
              <div>
                <dt>Role</dt>
                <dd><?= htmlspecialchars($entry['role'] ?? 'guest') ?></dd>
              </div>
              <div>
                <dt>IP</dt>
                <dd><?= htmlspecialchars($entry['ip'] ?? '') ?></dd>
              </div>
            </dl>
            <details>
              <summary>View context</summary>
              <pre><?= htmlspecialchars(
                json_encode($entry['context'] ?? new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
              ) ?></pre>
            </details>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </fieldset>
</div>

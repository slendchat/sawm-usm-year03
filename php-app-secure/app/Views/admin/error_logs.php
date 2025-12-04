<?php
/** @var array $entries */
?>
<div class="tui-window full-width">
  <fieldset class="tui-fieldset">
    <legend>Error Log</legend>
    <p>
      Здесь отображаются последние системные ошибки и исключения из файла <code>error.log</code>.
      Доступ имеет только администратор.
    </p>
  </fieldset>
</div>

<div class="tui-window full-width">
  <fieldset class="tui-fieldset">
    <legend>Latest Errors</legend>
    <?php if (empty($entries)): ?>
      <p>Ошибок пока не зафиксировано</p>
    <?php else: ?>
      <div class="log-card-list">
        <?php foreach ($entries as $entry): ?>
          <article class="log-card">
            <header>
              <div>
                <div class="log-card-action"><?= htmlspecialchars($entry['type'] ?? 'ERROR') ?></div>
                <small><?= htmlspecialchars($entry['timestamp'] ?? '') ?></small>
              </div>
            </header>
            <dl>
              <div>
                <dt>Message</dt>
                <dd><?= htmlspecialchars($entry['message'] ?? '') ?></dd>
              </div>
              <div>
                <dt>File</dt>
                <dd><?= htmlspecialchars($entry['file'] ?? '') ?>:<?= htmlspecialchars((string)($entry['line'] ?? '')) ?></dd>
              </div>
              <div>
                <dt>User ID</dt>
                <dd><?= htmlspecialchars((string)($entry['user_id'] ?? '—')) ?></dd>
              </div>
            </dl>
            <?php if (!empty($entry['trace'])): ?>
              <details>
                <summary>View trace</summary>
                <pre><?= htmlspecialchars(json_encode($entry['trace'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
              </details>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </fieldset>
</div>

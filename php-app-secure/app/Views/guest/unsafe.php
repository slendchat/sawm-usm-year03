<div class="guestbook">
  <div class="tui-window">
    <fieldset class="tui-fieldset">
      <legend>Небезопасный режим</legend>
      <p class="guestbook-lead">
        Эта страница выводит сообщения как есть без экранирования и нужна только для демонстрации XSS.
        Вернитесь на защищённую страницу, чтобы добавлять записи.
      </p>
      <div class="guestbook-actions">
        <span class="red-168-text">Не вводите здесь чувствительные данные.</span>
        <a href="/guestbook"><button class="tui-button" type="button">Защищённая версия</button></a>
      </div>
    </fieldset>
  </div>

  <div class="tui-window">
    <fieldset class="tui-fieldset">
      <legend>Сообщения</legend>
      <?php if (!$messages): ?>
        <p class="guestbook-empty">Сообщений пока нет.</p>
      <?php else: ?>
        <ul class="guestbook-list">
          <?php foreach ($messages as $entry): ?>
            <li class="guestbook-entry">
              <div class="guestbook-entry__meta">
                <span class="guestbook-entry__name"><?= $entry['user'] ?></span>
                <span>&lt;<?= $entry['e_mail'] ?>&gt;</span>
                <span class="guestbook-entry__time"><?= $entry['data_time_message'] ?></span>
              </div>
              <p class="guestbook-entry__message"><?= nl2br($entry['text_message']) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </fieldset>
  </div>
</div>

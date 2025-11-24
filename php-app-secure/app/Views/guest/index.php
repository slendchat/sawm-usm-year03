<div class="guestbook">
  <div class="guestbook-grid">
    <section>
      <div class="tui-window">
        <fieldset class="tui-fieldset">
          <legend>Гостевая книга</legend>
          <p class="guestbook-lead">
            Безопасная форма: весь текст экранируется, чтобы защититься от XSS.
            Сообщения появляются справа ниже.
          </p>
          <form action="/guestbook" method="post" data-xss-guard="false" class="guestbook-form">
            <div class="guestbook-field">
              <label for="guest-user">Имя</label>
              <input
                id="guest-user"
                class="tui-input"
                type="text"
                name="user"
                value="<?= htmlspecialchars($old['user'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
              >
            </div>
            <div class="guestbook-field">
              <label for="guest-email">E-mail</label>
              <input
                id="guest-email"
                class="tui-input"
                type="email"
                name="e_mail"
                value="<?= htmlspecialchars($old['e_mail'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
              >
            </div>
            <div class="guestbook-field">
              <label for="guest-message">Сообщение</label>
              <textarea
                id="guest-message"
                class="tui-textarea"
                name="text_message"
                rows="5"
                required
              ><?= htmlspecialchars($old['text_message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="guestbook-actions">
              <span class="orange-168-text">Ввод проверяется и экранируется.</span>
              <button class="tui-button" type="submit">Отправить сообщение</button>
            </div>
          </form>
        </fieldset>
      </div>
    </section>

    <section>
      <div class="tui-window">
        <fieldset class="tui-fieldset">
          <legend>Последние записи</legend>
          <?php if (!$messages): ?>
            <p class="guestbook-empty">Сообщений пока нет.</p>
          <?php else: ?>
            <ul class="guestbook-list">
              <?php foreach ($messages as $entry): ?>
                <li class="guestbook-entry">
                  <div class="guestbook-entry__meta">
                    <span class="guestbook-entry__name"><?= htmlspecialchars($entry['user'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span>&lt;<?= htmlspecialchars($entry['e_mail'], ENT_QUOTES, 'UTF-8') ?>&gt;</span>
                    <span class="guestbook-entry__time"><?= htmlspecialchars($entry['data_time_message'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <p class="guestbook-entry__message"><?= nl2br(htmlspecialchars($entry['text_message'], ENT_QUOTES, 'UTF-8')) ?></p>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <div class="guestbook-actions">
            <span class="red-168-text">Для демонстрации уязвимости:</span>
            <a href="/guestbook/unsafe"><button class="tui-button red-168" type="button">Небезопасная версия</button></a>
          </div>
        </fieldset>
      </div>
    </section>
  </div>
</div>

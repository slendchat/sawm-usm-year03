<h1>Гостевая книга</h1>
<p>Эта страница отображает сообщения безопасно. Введённый текст экранируется и не выполнит XSS.</p>

<form action="/guestbook" method="post" data-xss-guard="true">
  <div>
    <label>Имя:<br>
      <input type="text" name="user" value="<?= htmlspecialchars($old['user'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </label>
  </div>
  <div>
    <label>E-mail:<br>
      <input type="email" name="e_mail" value="<?= htmlspecialchars($old['e_mail'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </label>
  </div>
  <div>
    <label>Сообщение:<br>
      <textarea name="text_message" rows="4" cols="40" required><?= htmlspecialchars($old['text_message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    </label>
  </div>
  <button type="submit">Отправить сообщение</button>
</form>

<hr>

<h2>Последние записи</h2>
<?php if (!$messages): ?>
  <p>Сообщений пока нет.</p>
<?php else: ?>
  <?php foreach ($messages as $entry): ?>
    <article>
      <header>
        <strong><?= htmlspecialchars($entry['user'], ENT_QUOTES, 'UTF-8') ?></strong>
        &lt;<em><?= htmlspecialchars($entry['e_mail'], ENT_QUOTES, 'UTF-8') ?></em>&gt;
        — <time datetime="<?= htmlspecialchars($entry['data_time_message'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($entry['data_time_message'], ENT_QUOTES, 'UTF-8') ?></time>
      </header>
      <p><?= nl2br(htmlspecialchars($entry['text_message'], ENT_QUOTES, 'UTF-8')) ?></p>
    </article>
    <hr>
  <?php endforeach; ?>
<?php endif; ?>

<p><a href="/guestbook/unsafe">Посмотреть небезопасную версию для демонстрации XSS</a></p>

<h1>Гостевая книга (небезопасный режим)</h1>
<p>Эта страница выводит сообщения как есть без экранирования. Используйте её лишь для демонстрации XSS.</p>
<p><a href="/guestbook">Вернуться к защищённой версии</a></p>

<?php if (!$messages): ?>
  <p>Сообщений пока нет.</p>
<?php else: ?>
  <?php foreach ($messages as $entry): ?>
    <article>
      <header>
        <strong><?= $entry['user'] ?></strong>
        &lt;<em><?= $entry['e_mail'] ?></em>&gt;
        — <time datetime="<?= $entry['data_time_message'] ?>"><?= $entry['data_time_message'] ?></time>
      </header>
      <p><?= nl2br($entry['text_message']) ?></p>
    </article>
    <hr>
  <?php endforeach; ?>
<?php endif; ?>

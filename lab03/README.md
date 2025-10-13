# Лабораторная работа №3. Гостевая книга и защита от XSS

## Предварительные условия
1. **База данных `guest`** — выполнено. Добавлена миграция `php-app-secure/migrations/006_create_guest.sql:1`, создающая таблицу `guest(id, user, text_message, e_mail, data_time_message)`.
2. **Мини-приложение гостевой книги** — выполнено. В маршрутизаторе объявлены эндпоинты `/guestbook` (GET/POST) и `/guestbook/unsafe` (GET) `php-app-secure/public/index.php:57`. За логику отвечает `php-app-secure/app/Controllers/GuestController.php:15`, а шаблоны с формой/списком находятся в `php-app-secure/app/Views/guest/index.php:1` и `php-app-secure/app/Views/guest/unsafe.php:1`.

## Требования лабораторной работы
1. **Демонстрация XSS-атаки** — выполнено. Страница `/guestbook/unsafe` выводит записи без экранирования `php-app-secure/app/Views/guest/unsafe.php:11`, поэтому достаточно добавить сообщение с текстом `<script>alert('XSS');</script>` через форму (или напрямую в БД), чтобы при открытии небезопасной версии увидели всплывающее окно/подмену контента.
2. **Скрипты защиты от XSS** — выполнено. 
   - Серверная проверка: метод `containsXss()` и валидация в `GuestController::store()` блокируют подозрительные конструкции (`<script>`, event-атрибуты и т. п.) `php-app-secure/app/Controllers/GuestController.php:42` и `php-app-secure/app/Controllers/GuestController.php:96`.
   - Клиентская проверка: скрипт `php-app-secure/public/js/guest-xss-guard.js:1`, подключённый в макете `php-app-secure/app/Views/layout/header.php:19`, отменяет отправку формы, если ввод содержит шаблоны XSS. Безопасный шаблон гостевой книги экранирует вывод через `htmlspecialchars()` `php-app-secure/app/Views/guest/index.php:32` и использует `nl2br()` для форматирования `php-app-secure/app/Views/guest/index.php:36`.

## Проверка работы
1. Примените миграции (например, `mysql -u root -p guest < php-app-secure/migrations/006_create_guest.sql` или прогоните весь каталог миграций проекта).
2. Запустите приложение (`php -S 0.0.0.0:8000 -t php-app-secure/public`).
3. Перейдите на `/guestbook` и добавьте новое сообщение — запись появится в списке безопасной страницы.
4. Для демонстрации XSS сохраните сообщение с телом `<script>document.body.innerHTML = 'Hacked!';</script>`, затем откройте `/guestbook/unsafe` — страница будет изменена, что подтверждает уязвимость в небезопасном режиме.
5. Повторите попытку на защищённой странице (`/guestbook`) — отправка будет заблокирована клиентской проверкой или сервер вернёт ошибку, а сохранённые данные выводятся экранированными.
![alt text]({4DB0C013-44C4-4AF2-819A-6188C514EFB1}.png)
## Примечания
- Добавлена ссылка на гостевую книгу в навигации макета `php-app-secure/app/Views/layout/header.php:29`.
- Формы гостевой книги намеренно оставлены без CSS, чтобы сосредоточиться на функциональности и защите.
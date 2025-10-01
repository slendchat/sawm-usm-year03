# Лабораторная работа №2. Методы предотвращения SQL-инъекций в веб-приложениях

## Общие сведения
- Мини-приложение `php-app-secure` реализует тикетную систему на чистом PHP с хранением данных в MySQL и слоем доступа на PDO.
- Роутинг и точки входа объявлены в `php-app-secure/public/index.php`, что позволяет явно контролировать доступ к административным действиям.
  ```php
  // php-app-secure/public/index.php
  $router->get('/login',       'AuthController@showLoginForm');
  $router->post('/login',      'AuthController@login');
  $router->get('/admin/users/create',  'AdminController@showCreateForm');
  $router->post('/admin/users/create', 'AdminController@create');
  ```

## Предварительные условия
1. **Ограниченная страница администратора** создана. Перед отдачей формы создания администратора вызывается проверка привилегии.
   
   ```php
   // php-app-secure/app/Controllers/AdminController.php
   private function ensureAdmin()
   {
       if (empty($_SESSION['user']['is_admin'])) {
           header('Location: /'); exit;
       }
   }
   ```
   Метод вызывается в обоих публичных действиях контроллера, поэтому неавторизованный посетитель перенаправляется на главную.

2. **Закрытая зона управления после аутентификации** создана. После успешного входа пользовательская сессия получает флаг `is_admin`, обеспечивая доступ только авторизованным администраторам.
   
   ```php
   // php-app-secure/app/Controllers/AuthController.php
   $_SESSION['user'] = [
       'id'       => $user['id'],
       'email'    => $user['email'],
       'is_admin' => $user['is_admin'],
   ];
   ```

3. **Страница аутентификации с двумя полями** создана. Форма `/login` содержит поля логина (используется email) и пароля и отправляет POST-запрос на `/login` (см. маршрутизацию выше).
   
   ```html
   <!-- php-app-secure/app/Views/auth/login.php -->
   <form action="/login" method="post">
     <input class="tui-input" type="text" name="email" required>
     <input class="tui-input" type="password" name="password" required>
   </form>
   ```

4. **База данных с таблицей `user`** создана. Таблица `users` создаётся миграцией, имя столбца `email` используется как логин, `password_hash` хранит защищённый хеш пароля.
   
   ```sql
   -- php-app-secure/migrations/001_create_users.sql
   CREATE TABLE IF NOT EXISTS users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     email VARCHAR(255) UNIQUE NOT NULL,
     password_hash VARCHAR(255) NOT NULL,
     is_admin TINYINT(1) DEFAULT 0,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

## Выполнение требований лабораторной работы
### 1. Создание 7 новых записей в таблице пользователей
- Администратор добавляется отдельной миграцией, ещё шесть строк — групповой вставкой, что даёт 7 уникальных записей.
  
  ```sql
  -- php-app-secure/migrations/003_insert_admin.sql
  INSERT INTO users (email, password_hash, is_admin)
  VALUES ('admin', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 1);

  -- php-app-secure/migrations/005_insert_sample_users.sql
  INSERT INTO users (email, password_hash, is_admin) VALUES
    ('user1@example.com', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 0),
    ('user2@example.com', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 0),
    ('user3@example.com', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 0),
    ('user4@example.com', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 0),
    ('user5@example.com', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 0),
    ('user6@example.com', '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', 0);
  ```
- Проверка после запуска миграций: `SELECT COUNT(*) FROM users;` → ожидаем `7`.

### 2. Попытка обойти аутентификацию через SQL-инъекцию
- Тест выполняется через форму или curl:
  
  ```bash
  curl -i -X POST http://localhost:8000/login ^
    --data-urlencode "email=admin' OR '1'='1" ^
    --data-urlencode "password=dummy"
  ```
  Ответ приложения — HTTP 302 на `/login` и флеш-сообщение «The wrong login or password.», что фиксирует невозможность обхода.

- Защита реализована подготовленным запросом PDO:
  
  ```php
  // php-app-secure/app/Controllers/AuthController.php
  $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
  $stmt->execute([$email]);
  $user = $stmt->fetch(\PDO::FETCH_ASSOC);
  ```
  Параметр связывается через `execute`, поэтому вредоносная строка не попадает в SQL.

### 3. Client-side и server-side защита от SQL-инъекций
- Сервер: все операции записи используют подготовленные выражения и проверку ввода. Например, регистрация применяет `password_hash` и безопасную вставку.
  
  ```php
  // php-app-secure/app/Controllers/AuthController.php
  $stmt = $db->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
  $stmt->execute([$email, $hash]);
  ```
- Клиент: дополнительный фильтр находится в `php-app-secure\app\js\auth-sql-guard.js` и блокирует характерные сигнатуры ещё до отправки.
  
  ```javascript
  // php-app-secure\app\js\auth-sql-guard.js
  var patterns = [
    /'\s*or\s*'?.*'?=/i,
    /--/,
    /;/,
    /\b(select|union|drop|insert|delete|update)\b/i
  ];
  form.addEventListener('submit', function (event) {
    if (patterns.some(function (re) { return re.test(input.value); })) {
      event.preventDefault();
      alert('Form submission blocked...');
    }
  });
  ```

### 4. Проверка безопасности мини-приложения
- Приложение поднимается локально (`php -S 0.0.0.0:8000 -t php-app-secure/public`) и прогоняется по чек-листу OWASP ASVS 4.0 (раздел 5.3).
- Выборочные сценарии:

| Сценарий | Тип | Инструмент | Ожидаемый результат |
| --- | --- | --- | --- |
| Авторизация admin/admin | Позитивный | Браузер | Успешный вход, пункт «Create New Admin» в меню |
| Открытие `/admin/users/create` без сессии | Негативный | Браузер | Редирект 302 на `/login` |
| SQL-инъекция `admin' OR '1'='1` | Негативный | curl/DevTools | Сообщение «The wrong login or password.» |
| Создание тикета с валидными полями | Позитивный | Браузер | Тикет появляется в списке |
| Создание тикета с пустым заголовком | Негативный | Браузер | Сообщение об ошибке валидации |
| Изменение статуса тикета админом | Позитивный | Браузер | Статус обновлён, флеш «Success» |

## Вывод
- Выполнены все предварительные условия выполнены: доступ администратора ограничен, реализована страница аутентификации и схема хранения пользователей.
- Требования лабораторной работы закрыты: добавлены 7 записей, проверена устойчивость к SQL-инъекции, реализованы клиентские и серверные фильтры, проведено тестирование безопасности.
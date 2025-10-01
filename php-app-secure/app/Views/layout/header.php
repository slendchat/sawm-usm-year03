<!-- app/Views/layout/header.php -->
<!DOCTYPE html>
<html lang="ru" class="tui-bg-blue-black">
<head>
  <meta charset="UTF-8">
  <link rel="shortcut icon" type="image/x-icon" href="icon.png" />
  <title><?= htmlspecialchars($title ?? 'Ticket System') ?></title>
  <!-- Styles -->
  <link rel="stylesheet" href="/css/global.css">
  <link rel="stylesheet" href="/css/layout.css">
  <link rel="stylesheet" href="/css/admin.css">
  <link rel="stylesheet" href="/css/auth.css">
  <link rel="stylesheet" href="/css/home.css">
  <link rel="stylesheet" href="/css/tickets.css">
  <!--  -->
  <!-- TUI CSS -->
  <link rel="stylesheet" href="/dist/tuicss.min.css"/>
  <!--  -->
</head>
<body>
  <!-- TUI CSS SCRIPT -->
<script script src="/dist/tuicss.min.js"></script>
<!--  -->
  <nav class="tui-nav">
    <?php if(!empty($_SESSION['user'])): ?>
      <ul>
      <li><a href="/"><span class="red-168-text">M</span>ain</a></li> 
      <li><a href="/ticket/create"><span class="red-168-text">C</span>reate Ticket</a></li> 
      <li><a href="/tickets"><span class="red-168-text">T</span>icket List</a></li>
      <?php if(!empty($_SESSION['user']['is_admin'])): ?>
         <li><a href="/admin/users/create"><span class="red-168-text">C</span>reate New Admin</a></li>
      <?php endif; ?>  
      <li><a href="/logout"><span class="red-168-text">L</span>og Out</a></li>
      <span class="tui-datetime" data-format="H:m:s"></span>
      <span style="float:right; margin-right:1.5rem;">Welcome, <u><?=htmlspecialchars($_SESSION['user']['email'])?></u></span>
      </ul>
      <?php else: ?>
        <ul>
        <li><a href="/"><span class="red-168-text">M</span>ain</a></li>
        <li><a href="/tickets"><span class="red-168-text">T</span>icket List</a></li>
        <li><a href="/login"><span class="red-168-text">L</span>ogin</a></li> 
        <li><a href="/register"><span class="red-168-text">S</span>ign Up</a></li>
        <span class="tui-datetime" data-format="H:m:s"></span> 
        </ul>
      <?php endif; ?>
  </nav>
  <main>
<!-- ALERTS -->
  <?php if (!empty($_SESSION['success'])): ?>
  <div class="tui-window green-168" style="margin-bottom: 2rem;">
    <fieldset class="tui-fieldset">
      <legend class="yellow-255-text">Success</legend>
    <?= htmlspecialchars($_SESSION['success']) ?>
    </fieldset>
    </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="tui-window red-168" style="margin-bottom: 2rem;">
    <fieldset class="tui-fieldset">
      <legend class="yellow-255-text">Alert</legend>
      <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </fieldset>
  </div>
<?php endif; ?>
<!--  -->

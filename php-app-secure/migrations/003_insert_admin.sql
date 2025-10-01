INSERT INTO users (email, password_hash, is_admin)
VALUES (
  'admin',
  '$2y$10$pCm5pLPG0zJ/4Cm.N2odl.LDE2jzj9yLOrrQaA5V594rgx.7Tz3tO', -- password_hash('admin', PASSWORD_BCRYPT)
  1
);

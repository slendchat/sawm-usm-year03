INSERT INTO users (email, password_hash, is_admin, role)
VALUES (
  'manager',
  '$2y$10$/I4k.CcG6PgbK7BPzJoOr.NQcTNnmnXbV9bm4pLW1UM9M4PoaRJ1G', -- password_hash('manager')
  0,
  'manager'
);

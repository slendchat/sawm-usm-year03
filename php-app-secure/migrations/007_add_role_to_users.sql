ALTER TABLE users
  ADD COLUMN role ENUM('admin','manager') DEFAULT NULL AFTER is_admin;

UPDATE users
  SET role = 'admin'
  WHERE is_admin = 1;

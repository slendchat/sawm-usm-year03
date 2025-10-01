INSERT INTO tickets (user_id, title, description, category, priority, due_date, is_urgent) VALUES
(1, 'Database backup not running', 'Scheduled database backup is not executing and logs show no errors.', 'Backup', 'High', '2025-05-08', 1),
(1, 'LDAP authentication failure', 'Users cannot log in via LDAP; error in logs: "Invalid credentials".', 'Authentication', 'Critical', '2025-05-07', 1),
(1, 'Request new test VM', 'Need to provision and configure a VM for DevOps testing environment.', 'Server', 'Medium', '2025-05-15', 0),
(1, 'Configure Prometheus alerts', 'Add alert rule for CPU usage > 90% sustained for 5 minutes.', 'Monitoring', 'Medium', '2025-05-10', 0),
(1, 'Renew SSL certificate', 'SSL cert for domain.tld expires on 2025-05-30 – renew and deploy to Nginx.', 'Security', 'High', '2025-05-20', 1),
(1, 'Update backup documentation', 'Revise backup process guide in Confluence to match new scripts.', 'Documentation', 'Low', '2025-05-12', 0);

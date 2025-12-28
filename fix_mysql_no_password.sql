-- MySQL Access Fix - NO PASSWORD VERSION
-- Run this on your MySQL server (192.168.100.109)
-- Connect to MySQL: mysql -u root

-- ============================================
-- OPTION 1: Grant from specific hostname
-- ============================================
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net';
FLUSH PRIVILEGES;

-- ============================================
-- OPTION 2: Grant from any host (EASIEST)
-- ============================================
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;

-- ============================================
-- Verify it worked
-- ============================================
SHOW GRANTS FOR 'root'@'DESKTOP-6371IRP.mshome.net';
-- OR
SHOW GRANTS FOR 'root'@'%';

-- Check users
SELECT user, host FROM mysql.user WHERE user = 'root';








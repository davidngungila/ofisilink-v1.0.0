-- MySQL Access Fix Script (Correct Syntax for All MySQL Versions)
-- Run this on your MySQL server (192.168.100.109)
-- Connect to MySQL: mysql -u root -p

-- ============================================
-- FOR MySQL 8.0+ (New Syntax)
-- ============================================

-- Step 1: Create/Update user (if doesn't exist)
CREATE USER IF NOT EXISTS 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_password';

-- Step 2: Grant privileges
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net';

-- Step 3: Apply changes
FLUSH PRIVILEGES;

-- ============================================
-- OR: Grant from any host (easier)
-- ============================================

CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'your_mysql_password';
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;

-- ============================================
-- FOR MySQL 5.7 and below (Old Syntax)
-- ============================================

-- If CREATE USER IF NOT EXISTS doesn't work, use:
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_password';
FLUSH PRIVILEGES;

-- OR for any host:
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%' IDENTIFIED BY 'your_mysql_password';
FLUSH PRIVILEGES;

-- ============================================
-- Verify the grant
-- ============================================
SHOW GRANTS FOR 'root'@'DESKTOP-6371IRP.mshome.net';
-- OR
SHOW GRANTS FOR 'root'@'%';

-- ============================================
-- Check current users
-- ============================================
SELECT user, host FROM mysql.user WHERE user = 'root';








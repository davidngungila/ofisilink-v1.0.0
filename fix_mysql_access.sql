-- MySQL Access Fix Script for ZKBio Time.Net Sync
-- Run this on your MySQL server (192.168.100.109)
-- Connect to MySQL: mysql -u root -p

-- Step 1: Check current users and hosts
SELECT user, host FROM mysql.user WHERE user = 'root';

-- Step 2: Grant access from your Laravel server hostname
-- Replace 'your_mysql_password' with your actual MySQL root password
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_password';
FLUSH PRIVILEGES;

-- Step 3: OR grant access from any host on your network (easier, but less secure)
-- Uncomment the line below if you want to allow from any host:
-- GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%' IDENTIFIED BY 'your_mysql_password';
-- FLUSH PRIVILEGES;

-- Step 4: Verify the grant was successful
SHOW GRANTS FOR 'root'@'DESKTOP-6371IRP.mshome.net';

-- Step 5: Test if you can see the database
SHOW DATABASES LIKE 'ofisi';
USE ofisi;
SHOW TABLES;








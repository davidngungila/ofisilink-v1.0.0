# Fix MySQL Access - Step by Step

## Your Error
```
#1064 - You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version
```

This happens because MySQL 8.0+ changed the syntax. Use the correct version below.

---

## Solution: Choose Based on Your MySQL Version

### Check Your MySQL Version First:
```sql
SELECT VERSION();
```

---

## For MySQL 8.0 and Above (Recommended)

```sql
-- Step 1: Create user if doesn't exist
CREATE USER IF NOT EXISTS 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_password';

-- Step 2: Grant privileges
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net';

-- Step 3: Apply changes
FLUSH PRIVILEGES;
```

**OR (Easier - Allow from any host):**

```sql
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'your_mysql_password';
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;
```

---

## For MySQL 5.7 and Below

```sql
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_password';
FLUSH PRIVILEGES;
```

**OR (Easier - Allow from any host):**

```sql
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%' IDENTIFIED BY 'your_mysql_password';
FLUSH PRIVILEGES;
```

---

## If User Already Exists

If you get "User already exists" error, use:

```sql
-- For MySQL 8.0+
ALTER USER 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_password';
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net';
FLUSH PRIVILEGES;

-- OR for any host
ALTER USER 'root'@'%' IDENTIFIED BY 'your_mysql_password';
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;
```

---

## Quick Fix (Works for All Versions)

**Option 1: Grant from specific hostname**
```sql
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net';
FLUSH PRIVILEGES;
```

**Option 2: Grant from any host (EASIEST)**
```sql
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;
```

**Note:** If password is needed, set it separately:
```sql
ALTER USER 'root'@'%' IDENTIFIED BY 'your_mysql_password';
FLUSH PRIVILEGES;
```

---

## Verify It Worked

```sql
-- Check grants
SHOW GRANTS FOR 'root'@'DESKTOP-6371IRP.mshome.net';
-- OR
SHOW GRANTS FOR 'root'@'%';

-- Check users
SELECT user, host FROM mysql.user WHERE user = 'root';
```

---

## After Fixing MySQL, Update Device Settings

1. Go to: **Attendance Settings → Devices → Edit "UF 2000 HQ"**
2. **Step 3: UF200-S Config**
3. Set **Database Password**: Enter your MySQL root password
4. **Save**

---

## Test Connection

```bash
php test_mysql_connection.php
```

---

## Run Sync

```bash
php artisan attendance:sync-zkbiotime --device="UF 2000 HQ"
```








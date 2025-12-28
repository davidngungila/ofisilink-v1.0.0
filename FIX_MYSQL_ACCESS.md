# Fix MySQL Access for ZKBio Time.Net Sync

## Problem
Your Laravel application server (`DESKTOP-6371IRP.mshome.net`) cannot connect to MySQL server at `192.168.100.109` because MySQL is rejecting the connection.

## Solution: Grant MySQL Access

### Step 1: Connect to MySQL Server (192.168.100.109)

You need to access the MySQL server where your `ofisi` database is hosted.

**Option A: If MySQL is on the same machine as Laravel:**
```bash
mysql -u root -p
```

**Option B: If MySQL is on a remote server (192.168.100.109):**
- SSH/RDP to that server, then:
```bash
mysql -u root -p
```

### Step 2: Grant Access to Your Laravel Server

Once connected to MySQL, run these commands:

```sql
-- Check current user privileges
SELECT user, host FROM mysql.user WHERE user = 'root';

-- Grant access from your Laravel server hostname
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_mysql_root_password';
FLUSH PRIVILEGES;

-- OR grant access from any host (if you trust your network)
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%' IDENTIFIED BY 'your_mysql_root_password';
FLUSH PRIVILEGES;

-- Verify the grant
SHOW GRANTS FOR 'root'@'DESKTOP-6371IRP.mshome.net';
```

### Step 3: Check MySQL Configuration

Edit MySQL configuration file:

**Windows:** `C:\ProgramData\MySQL\MySQL Server X.X\my.ini`
**Linux:** `/etc/mysql/my.cnf` or `/etc/my.cnf`

Find `[mysqld]` section and ensure:
```ini
[mysqld]
bind-address = 0.0.0.0  # Allow connections from any IP
# OR specify your network
# bind-address = 192.168.100.109
```

**Restart MySQL service after changes:**
- Windows: Services → MySQL → Restart
- Linux: `sudo systemctl restart mysql`

### Step 4: Check Firewall

Ensure MySQL port (3306) is open:

**Windows Firewall:**
1. Windows Defender Firewall → Advanced Settings
2. Inbound Rules → New Rule
3. Port → TCP → 3306 → Allow

**Linux:**
```bash
sudo ufw allow 3306/tcp
```

### Step 5: Test Connection

From your Laravel server, test the connection:
```bash
mysql -h 192.168.100.109 -u root -p ofisi
```

If connection succeeds, you're good to go!

### Step 6: Run Sync Again

```bash
php artisan attendance:sync-zkbiotime --device="UF 2000 HQ"
```

---

## Alternative: Use IP Address Instead of Hostname

If hostname resolution is causing issues, you can grant access by IP:

```sql
-- Find your Laravel server's IP address
-- Then grant access by IP
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'YOUR_LARAVEL_SERVER_IP' IDENTIFIED BY 'your_mysql_root_password';
FLUSH PRIVILEGES;
```

---

## Troubleshooting

### Error: "Access denied for user"
- Check password is correct
- Verify user exists: `SELECT user, host FROM mysql.user;`
- Try granting with `@'%'` for any host

### Error: "Can't connect to MySQL server"
- Check MySQL service is running
- Verify `bind-address` allows remote connections
- Check firewall allows port 3306
- Verify network connectivity: `ping 192.168.100.109`

### Error: "Host is not allowed to connect"
- Run the GRANT command above
- Use `FLUSH PRIVILEGES;` after granting
- Check MySQL error log for details

---

## Quick Test Script

Create a test file `test_mysql_connection.php`:

```php
<?php
$host = '192.168.100.109';
$db = 'ofisi';
$user = 'root';
$pass = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✓ MySQL connection successful!\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}
```

Run: `php test_mysql_connection.php`








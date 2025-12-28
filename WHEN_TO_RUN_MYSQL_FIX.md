# When to Run MySQL Access Fix

## Quick Answer

**Run it NOW on the MySQL server (192.168.100.109)** - This is a one-time setup that needs to be done manually on the MySQL server.

---

## Option 1: Automatic Script (Easiest)

### On Windows MySQL Server:
1. Copy `fix_mysql_on_server.bat` to the MySQL server (192.168.100.109)
2. Double-click to run it
3. Done! ✅

### On Linux MySQL Server:
1. Copy `fix_mysql_on_server.sh` to the MySQL server (192.168.100.109)
2. Make it executable: `chmod +x fix_mysql_on_server.sh`
3. Run it: `./fix_mysql_on_server.sh`
4. Done! ✅

---

## Option 2: Manual SQL (If script doesn't work)

### Step 1: Connect to MySQL Server
On the MySQL server (192.168.100.109), open terminal/command prompt:

**Windows:**
```bash
mysql -u root
```

**Linux:**
```bash
mysql -u root
```

### Step 2: Run SQL Command
```sql
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%';
FLUSH PRIVILEGES;
```

### Step 3: Verify
```sql
SHOW GRANTS FOR 'root'@'%';
```

---

## Option 3: From Laravel (If MySQL allows initial connection)

If your Laravel server can already connect to MySQL (but gets denied), you can try:

```bash
php artisan mysql:fix-access --host=192.168.100.109 --database=ofisi --user=root
```

**Note:** This only works if MySQL allows the initial connection. If you get "not allowed to connect", you must use Option 1 or 2.

---

## When Does This Need to Be Done?

- ✅ **Once** - This is a one-time setup
- ✅ **Now** - Before running the sync command
- ✅ **After** - If you change MySQL server or network configuration

---

## After Running the Fix

1. **Test connection:**
   ```bash
   php test_mysql_connection.php
   ```

2. **Run sync:**
   ```bash
   php artisan attendance:sync-zkbiotime --device="UF 2000 HQ"
   ```

---

## Why Can't It Be Fully Automatic?

MySQL security requires that access grants be made from a connection that's already allowed. Since your Laravel server is being blocked, we need to grant access from the MySQL server itself (where access is already allowed).

Think of it like this:
- MySQL server = Your house
- Laravel server = A visitor
- GRANT command = Giving the visitor a key
- You need to be inside the house (MySQL server) to give out the key

---

## Summary

**Run it NOW on MySQL server (192.168.100.109) using one of the options above, then you can run the sync command from Laravel anytime!**








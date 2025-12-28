# Database Cleanup - Remove Orphan Tables

This script identifies and removes database tables that don't have corresponding migration files.

## Generated Files

1. **drop_orphan_tables.sql** - SQL script to drop all orphan tables
2. **orphan_tables.php** - PHP array listing all orphan tables
3. **remove_orphan_tables.php** - The analysis script

## Summary

- **Total tables in database:** 273
- **Tables with migrations:** 125
- **Orphan tables (no migrations):** 153

## Important Notes

⚠️ **BEFORE RUNNING THE SQL SCRIPT:**

1. **BACKUP YOUR DATABASE FIRST!**
   ```bash
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Review the orphan tables list** in `orphan_tables.php` to ensure you want to remove them all

3. **Some tables might be from external systems:**
   - Django tables (django_*)
   - Attendance system tables (att_*, acc_*)
   - Base system tables (base_*)
   - Mobile app tables (mobile_*)
   - Meeting system tables (meeting_*)
   - IClock integration tables (iclock_*)
   - Payroll system tables (payroll_*)

4. **Test in a development environment first!**

## How to Use

### Option 1: Review and Execute SQL Manually

1. Open `drop_orphan_tables.sql` in a text editor
2. Review the tables to be dropped
3. Execute in your MySQL client:
   ```sql
   source drop_orphan_tables.sql
   ```

### Option 2: Execute via Command Line

```bash
mysql -u username -p database_name < drop_orphan_tables.sql
```

### Option 3: Execute via Laravel Tinker

```php
DB::unprepared(file_get_contents('drop_orphan_tables.sql'));
```

## Regenerating the Script

If you add new migrations, you can regenerate the cleanup script:

```bash
php remove_orphan_tables.php
```

## Tables That Will Be Dropped

The following categories of tables will be removed:

- **Django Framework Tables** (if not using Django)
- **Legacy Attendance System Tables** (att_*, acc_*)
- **Base System Tables** (base_*)
- **Mobile App Tables** (mobile_*)
- **Meeting System Tables** (meeting_*)
- **IClock Integration Tables** (iclock_*)
- **Legacy Payroll Tables** (payroll_*)
- **Other Legacy Tables**

## Safety Checklist

- [ ] Database backup created
- [ ] Reviewed orphan_tables.php list
- [ ] Tested in development environment
- [ ] Verified no critical data in orphan tables
- [ ] Notified team members
- [ ] Ready to execute

## Rollback

If you need to rollback, restore from your backup:

```bash
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql
```


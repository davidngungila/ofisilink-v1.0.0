# 🔐 OfisiLink - Login Credentials

## Default Login Credentials

### Super Administrator (System Admin)
- **Email**: `admin@ofisi.com`
- **Password**: `password`
- **Role**: System Admin
- **Access**: Full system access with all permissions

### Test User Accounts
After running the seeder, you may have additional test users. Check the TestUsersSeeder for other accounts.

## 🚪 Login URL
```
http://127.0.0.1:8000/login
```

## 📝 Important Notes

### First Time Setup
If this is your first time, you need to:
1. **Run migrations**: `php artisan migrate`
2. **Run seeders**: `php artisan db:seed`
3. **Create storage link**: `php artisan storage:link`

### After Login
Once logged in, you'll be automatically redirected to your role-specific dashboard:
- **System Admin** → Admin Dashboard
- **CEO** → CEO Dashboard
- **HR Officer** → HR Dashboard
- **HOD** → HOD Dashboard
- **Accountant** → Accountant Dashboard
- **Staff** → Staff Dashboard

## 🔒 Security Reminder
⚠️ **Important**: Change the default password immediately after first login for security purposes.

## 🎯 Access To File Management
- **Digital Files**: `/modules/files/digital`
- **Physical Racks**: `/modules/files/physical`









# Test Users for Login Testing

## Login Credentials
**All test users use the same password:** `password123`

**Note:** After entering credentials, an OTP will be sent to the registered phone number.

---

## Test Users Created

### Admin Users

1. **System Admin**
   - Email: `testuser1@ofisi.com`
   - Phone: `2556122000001`
   - Employee ID: `EMP001`
   - Role: System Admin

2. **CEO**
   - Email: `testuser2@ofisi.com`
   - Phone: `2556122000002`
   - Employee ID: `EMP002`
   - Role: CEO

### Management Users

3. **HR Officer**
   - Email: `testuser3@ofisi.com`
   - Phone: `2556122000003`
   - Employee ID: `EMP003`
   - Role: HR Officer

4. **HOD (Head of Department)**
   - Email: `testuser4@ofisi.com`
   - Phone: `2556122000004`
   - Employee ID: `EMP004`
   - Role: HOD

5. **Accountant**
   - Email: `testuser5@ofisi.com`
   - Phone: `2556122000005`
   - Employee ID: `EMP005`
   - Role: Accountant

### Staff Users

6-15. **Staff Members**
   - Email: `testuser6@ofisi.com` through `testuser15@ofisi.com`
   - Phone: `2556122000006` through `2556122000015`
   - Employee ID: `EMP006` through `EMP015`
   - Role: Staff

---

## Additional Test Users from Seeder

If you ran the TestUsersSeeder, you also have:

- **Staff:** `staff@ofisi.com` / `password`
- **Accountant:** `accountant@ofisi.com` / `password`
- **HOD:** `hod@ofisi.com` / `password`
- **CEO:** `ceo@ofisi.com` / `password`
- **HR Officer:** `hr@ofisi.com` / `password`

---

## Commands to Manage Test Users

### Create More Test Users
```bash
php artisan users:seed-test --count=10
```

### Assign Roles to Existing Users
```bash
php artisan users:assign-roles
```

### Check User Credentials
```bash
php artisan user:check-credentials testuser1@ofisi.com
```

### Reset User Password
```bash
php artisan user:reset-password testuser1@ofisi.com
```

### Add Single User
```bash
php artisan user:add 2556122000016 --name="New User" --email="newuser@ofisi.com" --password="password123"
```

---

## Testing Different Roles

Use these users to test different role-based access:

- **System Admin:** Full system access
- **CEO:** Executive dashboard and reports
- **HR Officer:** Employee management, payroll access
- **HOD:** Department management, approval workflows
- **Accountant:** Financial operations, payroll processing
- **Staff:** Basic access, personal dashboard

---

## Important Notes

1. All test users have phone numbers for OTP verification
2. Users are set as active (`is_active = true`)
3. Each user has an associated employee record
4. Phone numbers follow Tanzanian format (starting with 255)
5. OTP expires after 10 minutes
6. Users can request OTP resend if needed








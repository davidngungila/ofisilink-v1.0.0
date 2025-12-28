# Email Configuration Guide for OfisiLink

## Gmail SMTP Configuration

Your application is now configured to use Gmail for sending email notifications.

### Required .env Settings

Add or update the following lines in your `.env` file:

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=davidngungila@gmail.com
MAIL_PASSWORD=tusr qvth tqlh uwgz
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=davidngungila@gmail.com
MAIL_FROM_NAME="OfisiLink System"
```

### Important Notes

1. **App Password**: The password `tusr qvth tqlh uwgz` is a Gmail App Password, not your regular Gmail password. This is required for 2-factor authentication enabled accounts.

2. **Security**: 
   - Never commit the `.env` file to version control
   - Keep your app password secure
   - If compromised, revoke and generate a new app password from Google Account settings

3. **Testing**: After updating the `.env` file, run:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Troubleshooting**:
   - If emails fail to send, verify the app password is correct
   - Check Gmail security settings allow "Less secure app access" (or use App Passwords)
   - Ensure port 587 is not blocked by firewall
   - Check Laravel logs: `storage/logs/laravel.log`

### Email Notifications

The system will send email notifications for:
- Leave request approvals/rejections
- File access requests
- Petty cash approvals
- Payroll processing
- Permission requests
- And other system notifications

All emails are sent from: **davidngungila@gmail.com** (OfisiLink System)





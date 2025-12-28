# SMS Sending Troubleshooting Guide

## Quick Debug Steps

### 1. Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```
Look for entries with "SMS" to see detailed error messages.

### 2. Test SMS Sending
```bash
php artisan sms:test 255712345678 --message="Test message"
```

### 3. Verify Configuration
Check your `.env` file has these settings:
```env
SMS_URL=https://messaging-service.co.tz/api/sms/v1/test/text/single
SMS_FROM=N-SMS
SMS_USERNAME=im23n
SMS_PASSWORD=23n23n
SMS_SSL_VERIFY=true
```

## Common Issues & Solutions

### Issue 1: Invalid Phone Number Format
**Error**: `Invalid phone number format! Expected: 255XXXXXXXXX`

**Solution**: 
- Phone must be 12 digits: `255` (country code) + 9 digits
- Examples: `255712345678`, `255767123456`
- The system will auto-format: `0712345678` → `255712345678`

### Issue 2: cURL SSL Error
**Error**: `cURL error: SSL certificate problem`

**Solution**: 
- If testing, temporarily set in `.env`: `SMS_SSL_VERIFY=false`
- For production, ensure SSL certificate is valid
- Check server can reach `messaging-service.co.tz`

### Issue 3: HTTP 401 Unauthorized
**Error**: `HTTP status: 401`

**Solution**: 
- Verify credentials in `.env`
- Check base64 encoding: should be `aW0yM246MjNuMjNu`
- Test: `echo -n "im23n:23n23n" | base64`

### Issue 4: HTTP 400 Bad Request
**Error**: `HTTP status: 400`

**Solution**: 
- Check JSON body format matches API spec
- Verify phone number is exactly 12 digits
- Ensure message is not empty
- Check logs for exact request body

### Issue 5: Network/Connection Issues
**Error**: `cURL error: Couldn't connect to server`

**Solution**: 
- Test connectivity: `ping messaging-service.co.tz`
- Check firewall/network restrictions
- Verify server has internet access
- Test URL manually: `curl -I https://messaging-service.co.tz`

## Testing the API Directly

### Using cURL (Command Line)
```bash
curl -X POST https://messaging-service.co.tz/api/sms/v1/test/text/single \
  -H "Authorization: Basic aW0yM246MjNuMjNu" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "from": "N-SMS",
    "to": "255712345678",
    "text": "Test message",
    "reference": "test123"
  }'
```

### Using PHP
```php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://messaging-service.co.tz/api/sms/v1/test/text/single');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'from' => 'N-SMS',
    'to' => '255712345678',
    'text' => 'Test message',
    'reference' => 'test123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic aW0yM246MjNuMjNu',
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
```

## Debug Information in Logs

The system logs detailed information:
- **Request details**: URL, phone, message length
- **Response details**: HTTP code, API response
- **Error details**: cURL errors, validation errors, exceptions

Check `storage/logs/laravel.log` for entries like:
- `SMS API Request` - Request being sent
- `SMS API Response` - Response received
- `SMS sent successfully` - Success confirmation
- `SMS sending cURL error` - Connection errors
- `SMS sending failed with HTTP status` - API errors

## Phone Number Format

The system automatically formats phone numbers:
- Input: `0712345678` → Database: `255712345678`
- Input: `712345678` → Database: `255712345678`
- Input: `+255712345678` → Database: `255712345678`
- Input: `255-712-345-678` → Database: `255712345678`

Final format in database: **`255XXXXXXXXX`** (12 digits)

## API Specifications

- **Endpoint**: `https://messaging-service.co.tz/api/sms/v1/test/text/single`
- **Method**: POST
- **Auth**: Basic (username:password base64 encoded)
- **Body Format**: JSON
  ```json
  {
    "from": "N-SMS",
    "to": "255712345678",
    "text": "Your message",
    "reference": "unique_reference"
  }
  ```

## Contact Support

If issues persist:
1. Check logs: `storage/logs/laravel.log`
2. Run test command: `php artisan sms:test`
3. Verify API credentials with SMS provider
4. Check network connectivity to API endpoint








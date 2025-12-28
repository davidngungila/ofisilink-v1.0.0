# Mobile API Setup Guide

## Prerequisites

1. Laravel 12.x
2. PHP 8.2+
3. MySQL/MariaDB database

## Installation Steps

### 1. Install Laravel Sanctum

Laravel Sanctum is required for API token authentication. Install it using Composer:

```bash
composer require laravel/sanctum
```

### 2. Publish Sanctum Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 3. Run Migrations

```bash
php artisan migrate
```

This will create the `personal_access_tokens` table needed for token storage.

### 4. Update User Model

Ensure your `User` model uses the `HasApiTokens` trait. Check `app/Models/User.php`:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

### 5. Configure Sanctum

Update `config/sanctum.php` if needed. Default configuration should work for most cases.

### 6. Update API Routes Authentication

The API routes in `routes/api.php` use `auth:sanctum` middleware. This is already configured in the routes file.

### 7. Test API Endpoints

Test the API using tools like Postman or cURL:

```bash
# Login
curl -X POST http://your-domain.com/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Use token in subsequent requests
curl -X GET http://your-domain.com/api/mobile/v1/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Alternative: Simple Token Authentication (Without Sanctum)

If you prefer not to use Sanctum, you can implement a simple token-based authentication:

1. Create a migration for API tokens:
```bash
php artisan make:migration create_api_tokens_table
```

2. Update the migration:
```php
Schema::create('api_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('token', 64)->unique();
    $table->string('device_name')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});
```

3. Create a model:
```bash
php artisan make:model ApiToken
```

4. Update `AuthApiController` to use custom tokens instead of Sanctum.

## Configuration

### CORS Configuration

If your mobile app is on a different domain, configure CORS in `config/cors.php`:

```php
'paths' => ['api/*', 'mobile/*'],
'allowed_origins' => ['*'], // Or specific domains
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

### Rate Limiting

API routes are rate-limited by default. Configure in `app/Providers/RouteServiceProvider.php` or use middleware.

## API Base URL

The mobile API is available at:
- Development: `http://localhost:8000/api/mobile/v1`
- Production: `https://your-domain.com/api/mobile/v1`

## Testing

### Using Postman

1. Import the API collection (if available)
2. Set base URL: `http://your-domain.com/api/mobile/v1`
3. Login to get token
4. Set token in Authorization header for all requests

### Using cURL

```bash
# Login
TOKEN=$(curl -X POST http://your-domain.com/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}' \
  | jq -r '.data.token')

# Use token
curl -X GET http://your-domain.com/api/mobile/v1/dashboard \
  -H "Authorization: Bearer $TOKEN"
```

## Troubleshooting

### Token Not Working

1. Check if Sanctum is properly installed
2. Verify `HasApiTokens` trait is in User model
3. Check token in database: `SELECT * FROM personal_access_tokens WHERE tokenable_id = USER_ID`

### 401 Unauthorized

1. Verify token is included in Authorization header
2. Check token format: `Bearer {token}`
3. Verify token hasn't expired
4. Check user is active

### 403 Forbidden

1. Check user has required role/permission
2. Verify route middleware configuration

### CORS Errors

1. Update `config/cors.php`
2. Clear config cache: `php artisan config:clear`

## Security Best Practices

1. **Always use HTTPS** in production
2. **Implement token expiration** - tokens should expire after inactivity
3. **Use refresh tokens** for long-lived sessions
4. **Rate limit API endpoints** to prevent abuse
5. **Validate all input** on both client and server
6. **Log API access** for security auditing
7. **Use strong passwords** and enforce password policies
8. **Implement 2FA** for sensitive operations (optional)

## Next Steps

1. Review API documentation in `MOBILE_API_DOCUMENTATION.md`
2. Test all endpoints
3. Implement mobile app using the API
4. Set up monitoring and logging
5. Configure production environment

## Support

For issues or questions:
- Check API documentation
- Review Laravel Sanctum documentation: https://laravel.com/docs/sanctum
- Contact development team








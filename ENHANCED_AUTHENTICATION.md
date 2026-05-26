# Enhanced Authentication Features

## Overview

Your HR Management System now includes a complete authentication system with advanced security features:

✅ **Secure Password Validation** - Comprehensive password strength checking  
✅ **JWT/Session Authentication** - Support for both token-based and session-based auth  
✅ **Role-Based Login Access** - Admin, HR, Employee role management  
✅ **Auto Redirect After Login** - Automatic dashboard routing based on user role  
✅ **Password Strength Indicator** - Real-time password validation on frontend  
✅ **Activity Logging** - Track login/logout events  
✅ **Account Status Management** - Enable/disable user accounts  

## 1. Secure Password Validation

### Password Requirements

All passwords must meet the following criteria:

- **Minimum 8 characters** - Prevents short, easily guessable passwords
- **Maximum 128 characters** - Standard database field limitation
- **Uppercase Letter** - At least one A-Z character
- **Lowercase Letter** - At least one a-z character
- **Number** - At least one 0-9 character
- **Special Character** - At least one !@#$%^&*() etc.

### Strength Levels

```
Weak    (0-39%)   - Does not meet all requirements
Fair    (40-59%)  - Meets basic requirements
Good    (60-79%)  - Meets most requirements
Strong  (80%+)    - Meets all requirements
```

### Usage

```php
use App\Services\PasswordValidator;

// Validate password strength
$result = PasswordValidator::validate('MyP@ssw0rd');
// Returns: [
//     'valid' => true,
//     'errors' => [],
//     'strength' => 100,
//     'level' => 'strong',
// ]

// Check if password is common
$isCommon = PasswordValidator::isCommonPassword('password123');
// Returns: true

// Hash password
$hashed = PasswordValidator::hash('MyP@ssw0rd');

// Verify password
$valid = PasswordValidator::verify('MyP@ssw0rd', $hashed);
```

## 2. JWT/Session Authentication

### Session-Based Authentication (Default)

Used for web browser access with server-side session management.

**Features:**
- CSRF token protection
- Secure HTTP-only cookies
- Session timeout (configurable)
- Automatic logout on expiry

**Login Flow:**
```
POST /login
  ↓
Validate credentials
  ↓
Session created (server-side)
  ↓
Cookie sent to browser
  ↓
Auto-redirect by role
```

### JWT Token Authentication

Used for API access and mobile applications.

**Features:**
- Stateless token-based authentication
- Bearer token in Authorization header
- Token expiration (24 hours default)
- Refresh token support

**Login Flow:**
```
POST /api/login
  ↓
Validate credentials
  ↓
Generate JWT token
  ↓
Return token + refresh token
  ↓
Client stores token
```

**Token Structure:**
```json
{
  "iss": "http://localhost",
  "sub": 1,
  "user_id": 1,
  "email": "admin@company.com",
  "role": "admin",
  "name": "Admin User",
  "iat": 1716797846,
  "exp": 1716884246
}
```

## 3. Role-Based Login Access

### Available Roles

#### Admin
- Full system access
- User management
- System configuration
- All features accessible
- **Dashboard:** `/admin/dashboard`

#### HR
- Employee management
- Payroll processing
- Attendance tracking
- Leave management
- HR reports
- **Dashboard:** `/hr/dashboard`

#### Employee
- Personal dashboard
- Employee information
- Basic statistics
- Limited access
- **Dashboard:** `/dashboard`

### Role-Based Routes

```php
// Admin only
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/dashboard', ...);
});

// HR only
Route::middleware('role:hr')->group(function () {
    Route::get('/hr/dashboard', ...);
});

// Any authenticated user
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', ...);
});
```

### Using Roles in Code

```php
$user = Auth::user();

// Check role
if ($user->role === 'admin') {
    // Admin-specific code
}

// Using helper methods
if ($user->isAdmin()) { /* ... */ }
if ($user->isHR()) { /* ... */ }
if ($user->isActive()) { /* ... */ }

// Change role
$user->update(['role' => 'hr']);
```

## 4. Auto Redirect After Login

Users are automatically redirected to their role-specific dashboard after login.

### Redirect Logic

```php
Admin      → /admin/dashboard
HR         → /hr/dashboard
Employee   → /dashboard
```

### Customizing Redirects

Edit `AuthController::redirectByRole()` to customize redirect logic:

```php
private function redirectByRole($user)
{
    return match ($user->role) {
        'admin' => redirect('/admin/dashboard'),
        'hr' => redirect('/hr/dashboard'),
        'manager' => redirect('/manager/dashboard'),  // Custom role
        default => redirect('/dashboard'),
    };
}
```

## 5. API Endpoints

### Web Authentication

```
GET    /login              - Login form
POST   /login              - Login (session)
POST   /logout             - Logout
GET    /register           - Registration form
POST   /register           - Register new user
```

### API Authentication (JWT)

```
POST   /api/login                  - Login and get JWT token
POST   /api/register               - Register new user
POST   /api/validate-password      - Validate password strength
POST   /api/logout                 - Logout (requires JWT)
POST   /api/refresh-token          - Refresh JWT token
GET    /api/user                   - Get current user (requires JWT)
```

### API Request Examples

#### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@company.com",
    "password": "MyP@ssw0rd"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "abcd1234efgh5678ijkl9012mnop...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@company.com",
    "role": "admin"
  }
}
```

#### Using JWT Token
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

#### Refresh Token
```bash
curl -X POST http://localhost:8000/api/refresh-token \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

#### Validate Password
```bash
curl -X POST http://localhost:8000/api/validate-password \
  -H "Content-Type: application/json" \
  -d '{"password": "MyP@ssw0rd"}'
```

**Response:**
```json
{
  "valid": true,
  "strength": 100,
  "level": "strong",
  "errors": [],
  "is_common": false
}
```

## 6. Files and Structure

### Authentication Controllers
- `app/Http/Controllers/AuthController.php` - Main authentication logic

### Middleware
- `app/Http/Middleware/CheckRole.php` - Role-based access control
- `app/Http/Middleware/JWTAuthenticate.php` - JWT validation

### Services
- `app/Services/PasswordValidator.php` - Password validation & hashing
- `app/Services/JWTTokenService.php` - JWT token generation & validation

### Requests
- `app/Http/Requests/LoginRequest.php` - Login form validation
- `app/Http/Requests/RegisterRequest.php` - Registration validation

### Views
- `resources/views/auth/login.blade.php` - Login page
- `resources/views/auth/register.blade.php` - Registration page with strength indicator
- `resources/views/dashboard.blade.php` - Employee dashboard
- `resources/views/admin/dashboard.blade.php` - Admin dashboard
- `resources/views/hr/dashboard.blade.php` - HR dashboard

### Routes
- `routes/web.php` - Web routes with authentication & API routes

## 7. Security Features

### Password Security
- Passwords hashed with bcrypt (BCRYPT_ROUNDS=12)
- Strength validation prevents weak passwords
- Common password checking
- Confirmed password in registration

### Session Security
- Session regeneration on login
- CSRF token protection
- Secure HTTP-only cookies
- Session invalidation on logout
- Session timeout after 120 minutes (configurable)

### JWT Security
- HS256 signing algorithm
- Token expiration (24 hours)
- Refresh token support
- Token verification on each request

### Account Security
- Active/Inactive status checking
- Failed login attempt logging
- Last login tracking
- IP address logging

### Database Security
- Password hashing required
- Email uniqueness constraint
- Role validation (enum)
- Active status enforcement

## 8. Configuration

### Session Configuration
Edit `config/session.php`:
```php
'lifetime' => 120,              // 120 minutes
'expire_on_close' => false,     // Don't expire on browser close
'encrypt' => false,             // Use HTTPS in production
'http_only' => true,            // Prevent JavaScript access
'same_site' => 'lax',           // CSRF protection
```

### JWT Configuration
Edit `app/Services/JWTTokenService.php`:
```php
// Token expiration time (seconds)
const TOKEN_EXPIRY = 86400;  // 24 hours

// Refresh token expiry
const REFRESH_TOKEN_EXPIRY = 604800;  // 7 days
```

### Password Requirements
Edit `app/Services/PasswordValidator.php`:
- Modify regex patterns for different requirements
- Adjust minimum/maximum length
- Add/remove special characters

## 9. Testing

### Test Credentials
```
Admin:    admin@company.com / Abc12345!
HR:       hr@company.com / Abc12345!
Employee: john@company.com / Abc12345!
```

### Manual Testing

**Web Login:**
1. Navigate to `http://localhost:8000/login`
2. Enter admin credentials
3. Verify redirect to `/admin/dashboard`

**API Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@company.com","password":"Abc12345!"}'
```

**Password Validation:**
1. Go to registration page
2. Enter different passwords
3. Watch strength indicator update in real-time
4. Verify all requirements are met before registration

**Role-Based Access:**
1. Login as different roles
2. Verify dashboard redirect
3. Try accessing role-specific routes
4. Verify 403 Forbidden for unauthorized access

## 10. Common Issues & Solutions

### JWT Token Not Working
**Issue:** "Invalid token" error  
**Solution:** 
- Verify token format is `Bearer <token>`
- Check token expiration time
- Ensure Authorization header is present

### Password Validation Too Strict
**Issue:** Can't create password  
**Solution:**
- Use uppercase, lowercase, number, special character
- Minimum 8 characters
- Example: `MyP@ssw0rd`

### Session Expires Too Quickly
**Issue:** Gets logged out unexpectedly  
**Solution:**
- Check `config/session.php` lifetime setting
- Increase `SESSION_LIFETIME` in `.env`

### Role Middleware Not Working
**Issue:** Getting 403 errors for allowed roles  
**Solution:**
- Verify middleware registration in `bootstrap/app.php`
- Check user role in database
- Clear cache: `php artisan cache:clear`

## 11. Best Practices

1. **Always use HTTPS** in production
2. **Never log passwords** even in debug mode
3. **Rotate JWT tokens** periodically
4. **Update password requirements** as needed
5. **Monitor failed login** attempts for security
6. **Use strong session secrets** in production
7. **Implement account lockout** after failed attempts
8. **Require password change** on first login
9. **Enable two-factor authentication** for admins
10. **Regular security audits** of authentication code

## 12. Troubleshooting

### Check JWT Installation
```bash
php artisan tinker
>>> use Firebase\JWT\JWT;
>>> // Should load without error
```

### Verify Password Validator
```bash
php artisan tinker
>>> use App\Services\PasswordValidator;
>>> PasswordValidator::validate('MyP@ssw0rd');
```

### Test Authentication
```bash
php artisan tinker
>>> use App\Models\User;
>>> $user = User::first();
>>> Auth::login($user);
>>> Auth::check(); // Should return true
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

---

**Last Updated:** May 26, 2026  
**Version:** 2.0 - Enhanced Authentication  
**Status:** Production Ready

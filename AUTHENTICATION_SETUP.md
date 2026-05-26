# HR Management System - Authentication Setup Guide

## Overview
This authentication system provides a secure login page for the HR Management System with role-based access control. It supports three user roles:
- **Admin**: Full system access and administration privileges
- **HR**: Human Resources personnel access with HR-specific features
- **Employee**: Standard employee access

## Features

✅ **Professional Login Interface** - Clean, modern HR/payroll system design  
✅ **Role-Based Authentication** - Different dashboards for Admin, HR, and Employee roles  
✅ **Secure Password Handling** - Passwords are hashed using Laravel's built-in encryption  
✅ **Account Status Management** - Enable/disable user accounts  
✅ **Login Activity Tracking** - Tracks last login timestamp  
✅ **Middleware Protection** - Role-based route protection  
✅ **Responsive Design** - Works on desktop and mobile devices  

## Database Structure

### Users Table Columns
- `id` - Primary key
- `name` - User's full name
- `email` - User's email address (unique)
- `password` - Hashed password
- `role` - User role (admin, hr, employee)
- `is_active` - Account status (true/false)
- `last_login_at` - Last login timestamp
- `email_verified_at` - Email verification status
- `remember_token` - Session token
- `created_at` - Account creation date
- `updated_at` - Last updated date

## Setup Instructions

### 1. Run Database Migration
Execute the migration to add role and status columns to the users table:

```bash
php artisan migrate
```

This will:
- Create/update the users table with new columns
- Create password reset tokens table
- Create sessions table

### 2. Seed Test Data
Populate the database with test users:

```bash
php artisan db:seed
```

This creates the following test users:

| Email | Password | Role |
|-------|----------|------|
| admin@company.com | password | Admin |
| hr@company.com | password | HR |
| john@company.com | password | Employee |
| jane@company.com | password | Employee |

### 3. Run the Application
Start the Laravel development server:

```bash
php artisan serve
```

Navigate to: `http://localhost:8000/login`

## User Roles & Access

### Admin Dashboard (`/admin/dashboard`)
- Full system access
- User management capabilities
- System configuration access
- Administrative actions
- **Access Restriction**: Only users with `role = 'admin'`

### HR Dashboard (`/hr/dashboard`)
- Employee management
- Payroll processing
- Attendance tracking
- Leave request management
- **Access Restriction**: Only users with `role = 'hr'`

### Employee Dashboard (`/dashboard`)
- Personal dashboard
- Employee information view
- Basic statistics
- **Access Restriction**: Authenticated users with any role

## Route Structure

```
Public Routes:
  GET  /                     - Welcome page
  GET  /login                - Login form (guest only)
  POST /login                - Login submission (guest only)

Protected Routes (Authenticated Users):
  GET  /dashboard            - Generic dashboard
  POST /logout               - Logout
  GET  /admin/dashboard      - Admin only (requires role:admin)
  GET  /hr/dashboard         - HR only (requires role:hr)
```

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AuthController.php      - Authentication logic
│   └── Middleware/
│       └── CheckRole.php           - Role validation middleware

database/
├── factories/
│   └── UserFactory.php             - User factory with role helpers
├── migrations/
│   └── 2026_05_26_000000_add_role_to_users_table.php
└── seeders/
    └── DatabaseSeeder.php          - Test data creation

resources/
└── views/
    ├── auth/
    │   └── login.blade.php         - Login page
    ├── admin/
    │   └── dashboard.blade.php     - Admin dashboard
    ├── hr/
    │   └── dashboard.blade.php     - HR dashboard
    ├── dashboard.blade.php         - Employee/Generic dashboard
    └── errors/
        └── 403.blade.php           - Access denied page

routes/
└── web.php                         - Web routes with authentication
```

## Security Features

1. **Password Hashing** - All passwords are hashed using bcrypt algorithm
2. **CSRF Protection** - All forms include CSRF tokens
3. **Session Management** - Secure session handling with regeneration on login
4. **Active Status Check** - Inactive accounts are logged out automatically
5. **Role-Based Middleware** - Routes are protected by role-based middleware
6. **Login Activity Tracking** - Last login timestamp is recorded

## User Management

### Model Methods

The User model includes helpful methods:

```php
$user->isAdmin()      // Check if user is admin
$user->isHR()         // Check if user is HR
$user->isActive()     // Check if account is active
```

### Creating New Users

```php
// Create an admin user
User::create([
    'name' => 'New Admin',
    'email' => 'newadmin@company.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_active' => true,
]);

// Using factory
User::factory()->admin()->create([
    'email' => 'admin2@company.com',
    'name' => 'Admin Two',
]);
```

## Customization

### Change Test Credentials
Edit `database/seeders/DatabaseSeeder.php` to modify test user details

### Customize Dashboards
- Admin: `resources/views/admin/dashboard.blade.php`
- HR: `resources/views/hr/dashboard.blade.php`
- Employee: `resources/views/dashboard.blade.php`

### Modify Login Page
Edit `resources/views/auth/login.blade.php` to customize:
- Colors and styling
- Form fields
- Feature list
- Welcome message

### Add New Roles
To add a new role (e.g., 'manager'):

1. Update migration to include new role in enum
2. Add factory method in UserFactory
3. Create corresponding controller method
4. Add dashboard view
5. Create route with middleware

## Troubleshooting

### Issue: Login redirects to login page
**Solution**: Ensure `.env` file has correct database credentials

### Issue: Middleware not found
**Solution**: Run `composer dump-autoload` to regenerate autoload files

### Issue: CSS not loading on login page
**Solution**: The page uses Tailwind CSS via CDN. Ensure internet connection or use local Tailwind build

### Issue: Can't logout
**Solution**: Check that form method is POST and includes @csrf token

### Issue: Role middleware not working
**Solution**: Verify middleware is registered in `bootstrap/app.php`

## Testing Login

1. Navigate to `http://localhost:8000/login`
2. Use test credentials:
   - **Admin**: `admin@company.com` / `password`
   - **HR**: `hr@company.com` / `password`
   - **Employee**: `john@company.com` / `password`

3. Verify you're redirected to the correct dashboard based on role

## Environment Setup

Ensure your `.env` file has:

```env
APP_NAME="HR Management System"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:your_app_key_here

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_system
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

## Additional Notes

- All timestamps use application timezone (set in `config/app.php`)
- Passwords should be changed from defaults in production
- Consider implementing email verification for production
- Enable HTTPS in production environment
- Regular backups of user data are recommended
- Log access attempts for security auditing

## Support

For issues or feature requests, please refer to the main application documentation.

---

**Created**: May 26, 2026  
**Version**: 1.0  
**Status**: Production Ready

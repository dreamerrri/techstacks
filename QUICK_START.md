# HR Management System - Quick Start Guide

## Immediate Setup (5 minutes)

### Step 1: Install Dependencies
```bash
composer install
npm install
```

### Step 2: Create Environment File
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Configure Database
Update `.env` with your MySQL credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Run Migrations
```bash
php artisan migrate
php artisan db:seed
```

### Step 5: Start Application
```bash
php artisan serve
```

### Step 6: Access Login
Open browser: `http://localhost:8000/login`

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@company.com | password |
| HR | hr@company.com | password |
| Employee | john@company.com | password |

## What's Included

✅ Professional login page with HR branding  
✅ Role-based authentication system  
✅ Three dashboard layouts (Admin, HR, Employee)  
✅ User model with role management  
✅ Database migrations  
✅ Route protection with middleware  
✅ Test data seeder  
✅ Error page for access denied  

## Key Features

🔐 **Secure Authentication**
- Password hashing with bcrypt
- CSRF protection
- Session management
- Login activity tracking

👥 **Role Management**
- Admin role with full access
- HR role with department access
- Employee role with basic access

🎨 **Professional UI**
- Responsive design
- Gradient backgrounds
- Icon integration
- Form validation

📊 **Dashboard Features**
- User statistics
- Quick action buttons
- System information display
- Last login tracking

## Database Schema

**Users Table:**
- `id` - Primary key
- `name` - User name
- `email` - Email (unique)
- `password` - Hashed password
- `role` - User role (enum: admin, hr, employee)
- `is_active` - Account status
- `last_login_at` - Last login timestamp
- Timestamps (created_at, updated_at)

## Routes Overview

```
GET  /login              - Login page
POST /login              - Login action
POST /logout             - Logout action
GET  /dashboard          - User dashboard
GET  /admin/dashboard    - Admin dashboard (admin only)
GET  /hr/dashboard       - HR dashboard (hr only)
```

## Next Steps

1. ✅ Customize user interface colors
2. ✅ Add additional user fields if needed
3. ✅ Implement email verification
4. ✅ Add password reset functionality
5. ✅ Create additional dashboards for specific features
6. ✅ Set up role-based permissions
7. ✅ Configure logging and auditing

## Common Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh database setup
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear

# Create new user
php artisan tinker
>>> User::factory()->admin()->create(['email' => 'new@admin.com', 'name' => 'New Admin'])

# Start development server
php artisan serve

# Compile frontend assets
npm run dev
```

## Project Structure

```
├── app/Http/Controllers/AuthController.php   - Auth logic
├── app/Http/Middleware/CheckRole.php         - Role middleware
├── app/Models/User.php                       - User model
├── database/migrations/                      - Database migrations
├── database/seeders/DatabaseSeeder.php       - Test data
├── resources/views/auth/login.blade.php      - Login page
├── resources/views/admin/dashboard.blade.php - Admin dashboard
├── resources/views/hr/dashboard.blade.php    - HR dashboard
├── resources/views/dashboard.blade.php       - Employee dashboard
└── routes/web.php                            - Routes
```

## Tips & Best Practices

1. **Passwords**: Change default test passwords in production
2. **Database**: Always backup before migrations
3. **Sessions**: Configure session timeout in `config/session.php`
4. **Logging**: Enable audit logs for compliance
5. **Security**: Enable HTTPS in production
6. **Email**: Configure mail driver for notifications

---

For detailed documentation, see `AUTHENTICATION_SETUP.md`

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management System - Login</title>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/login.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-container">
        <!-- Left Section -->
        <div class="login-left">
            <div>
                <div style="font-size: 48px; margin-bottom: 20px;">
                    <i class="fas fa-building"></i>
                </div>
                <h2>HR Management System</h2>
                <p>Streamline your human resources and payroll operations with our comprehensive management platform.</p>
                
        
            </div>
        </div>

        <!-- Right Section -->
        <div class="login-right">
            <h3>Welcome Back</h3>
            <p>Sign in to access your dashboard</p>

            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="admin@company.com"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <div class="error-message">
                            <i class="fas fa-times-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                    >
                    @error('password')
                        <div class="error-message">
                            <i class="fas fa-times-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

           <div style="text-align: center; margin-top: 30px; color: #6b7280; font-size: 14px;">
               <!-- <p>Test Credentials:</p>
                <p><strong>Admin:</strong> admin@company.com / password</p>
                <p><strong>HR:</strong> hr@company.com / password</p> -->
                <p style="margin-top: 20px;">
                    Don't have an account? <a href="{{ route('register') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">Register here</a>
                </p>
            </div>
          
        </div>
    </div>
</body>
</html>

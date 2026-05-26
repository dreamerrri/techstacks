<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 900px;
            width: 90%;
            overflow: hidden;
        }

        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left h2 {
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .login-left p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        .features {
            text-align: left;
            width: 100%;
        }

        .features li {
            list-style: none;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features i {
            font-size: 20px;
            color: #fbbf24;
        }

        .login-right {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right h3 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-right p {
            color: #6b7280;
            margin-bottom: 40px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            color: #6b7280;
            font-weight: 400;
        }

        .remember-forgot a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #15803d;
            border-left: 4px solid #22c55e;
        }

        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error-message i {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }

            .login-left h2 {
                font-size: 24px;
            }
        }
    </style>
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
                
                <ul class="features">
                    <li>
                        <i class="fas fa-users"></i>
                        <span>Employee Management</span>
                    </li>
                    <li>
                        <i class="fas fa-money-bill"></i>
                        <span>Payroll Processing</span>
                    </li>
                    <li>
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance Tracking</span>
                    </li>
                    <li>
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics & Reports</span>
                    </li>
                    <li>
                        <i class="fas fa-lock"></i>
                        <span>Secure & Reliable</span>
                    </li>
                </ul>
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
                <p>Test Credentials:</p>
                <p><strong>Admin:</strong> admin@company.com / password</p>
                <p><strong>HR:</strong> hr@company.com / password</p>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management System - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<<<<<<< HEAD
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
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
            background: linear-gradient(135deg, #22ce9d 0%, #a6f3e0 100%);
            color: #064e3b;
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
            border-color: #22ce9d;
            box-shadow: 0 0 0 3px rgba(34, 206, 157, 0.12);
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
            color: #22ce9d;
            text-decoration: none;
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #22ce9d 0%, #16a085 100%);
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
            box-shadow: 0 5px 20px rgba(34, 206, 157, 0.4);
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
            background-color: #d9f8ef;
            color: #064e3b;
            border-left: 4px solid #22ce9d;
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
=======
    @vite(['resources/css/login.css', 'resources/js/app.js'])
>>>>>>> 41e6c0d28a9469a2871a765e3f245f872eebd9e8
</head>
<body>
    <div class="login-container">
        <!-- Left Section -->
        <!-- or images/techstack_ico.png for logo without text -->
       <div class="login-left">
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="margin-bottom: 20px;">
            <img src="{{ asset('images/techstackfull_ico.png') }}"  
                 alt="Techstack Logo" 
                 style="width: 150px;">
        </div>
                <h2>LogiPay</h2>
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

            <form method="POST" action="{{ route('login') }}" novalidate id="loginForm">
                @csrf

                {{-- Email --}}
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

                {{-- Password with show/hide toggle --}}
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div style="position: relative;">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            style="width: 100%; padding-right: 45px;"
                        >
                        <button type="button" onclick="togglePassword()"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                       background:none; border:none; cursor:pointer; color:#6b7280; padding:0;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-message">
                            <i class="fas fa-times-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                {{-- Submit with loading state --}}
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt" id="loginIcon"></i>
                    <span id="loginText">Sign In</span>
                </button>
            </form>

            <div style="text-align: center; margin-top: 30px; color: #6b7280; font-size: 14px;">
<<<<<<< HEAD
                <p>Test Credentials:</p>
                <p><strong>Admin:</strong> admin@company.com / password</p>
                <p><strong>HR:</strong> hr@company.com / password</p>
                <p style="margin-top: 20px;">
                    Don't have an account? <a href="{{ route('register') }}" style="color: #22ce9d; text-decoration: none; font-weight: 600;">Register here</a>
=======
                <p>
                    Don't have an account?
                    <a href="{{ route('register') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">
                        Register here
                    </a>
>>>>>>> 41e6c0d28a9469a2871a765e3f245f872eebd9e8
                </p>
            </div>
        </div>
    </div>

    <script>
        // Show / Hide password
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn  = document.getElementById('loginBtn');
            const icon = document.getElementById('loginIcon');
            const text = document.getElementById('loginText');

            btn.disabled       = true;
            icon.className     = 'fas fa-spinner fa-spin';
            text.textContent   = ' Signing in...';
        });
    </script>
</body>
</html>
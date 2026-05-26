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
                <p>
                    Don't have an account?
                    <a href="{{ route('register') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">
                        Register here
                    </a>
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
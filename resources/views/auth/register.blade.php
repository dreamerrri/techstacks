<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HR Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            max-width: 500px;
            width: 90%;
        }

        .register-container h2 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .register-container p {
            color: #6b7280;
            margin-bottom: 30px;
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
            box-shadow: 0 0 0 3px rgba(34, 206, 157, 0.15);
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .password-strength {
            margin-top: 10px;
            display: none;
        }

        .strength-meter {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }

        .strength-bar.weak {
            width: 25%;
            background: #dc2626;
        }

        .strength-bar.fair {
            width: 50%;
            background: #f59e0b;
        }

        .strength-bar.good {
            width: 75%;
            background: #3b82f6;
        }

        .strength-bar.strong {
            width: 100%;
            background: #22ce9d;
        }

        .strength-text {
            font-size: 12px;
            font-weight: 600;
        }

        .strength-text.weak {
            color: #dc2626;
        }

        .strength-text.fair {
            color: #f59e0b;
        }

        .strength-text.good {
            color: #3b82f6;
        }

        .strength-text.strong {
            color: #22ce9d;
        }

        .password-requirements {
            background: #f3f4f6;
            border-radius: 5px;
            padding: 12px;
            margin-top: 10px;
            font-size: 12px;
            display: none;
        }

        .requirement {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
        }

        .requirement.met {
            color: #22ce9d;
        }

        .requirement i {
            width: 14px;
            text-align: center;
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

        .register-btn {
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
            margin-top: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(34, 206, 157, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .register-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #6b7280;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 30px 20px;
            }

            .register-container h2 {
                font-size: 24px;
            }
        }
    </style>
=======
    @vite(['resources/css/register.css', 'resources/js/app.js'])
>>>>>>> 41e6c0d28a9469a2871a765e3f245f872eebd9e8
</head>
<body>
    <div class="register-container">
        <h2>Create Account</h2>
        <p>Join our HR Management System</p>

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
            @csrf

            <div class="form-group">
                <label for="name">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="John Doe"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="error-message">
                        <i class="fas fa-times-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="john@company.com"
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
                    autocomplete="new-password"
                >
                
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-meter">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Password Strength: Weak</div>
                </div>

                <div class="password-requirements" id="passwordRequirements">
                    <div class="requirement" id="req-length">
                        <i class="fas fa-circle"></i>
                        <span>At least 8 characters</span>
                    </div>
                    <div class="requirement" id="req-upper">
                        <i class="fas fa-circle"></i>
                        <span>One uppercase letter (A-Z)</span>
                    </div>
                    <div class="requirement" id="req-lower">
                        <i class="fas fa-circle"></i>
                        <span>One lowercase letter (a-z)</span>
                    </div>
                    <div class="requirement" id="req-number">
                        <i class="fas fa-circle"></i>
                        <span>One number (0-9)</span>
                    </div>
                    <div class="requirement" id="req-special">
                        <i class="fas fa-circle"></i>
                        <span>One special character (!@#$%^&*)</span>
                    </div>
                </div>

                @error('password')
                    <div class="error-message">
                        <i class="fas fa-times-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                >
                @error('password_confirmation')
                    <div class="error-message">
                        <i class="fas fa-times-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="register-btn" id="registerBtn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Login here</a>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const passwordRequirements = document.getElementById('passwordRequirements');
        const registerBtn = document.getElementById('registerBtn');

        const requirements = {
            length: /^.{8,}$/,
            upper: /[A-Z]/,
            lower: /[a-z]/,
            number: /[0-9]/,
            special: /[!@#$%^&*()_+\-=\[\]{};:'",.< >?\/\\|`~]/,
        };

        passwordInput.addEventListener('input', function() {
            const password = this.value;

            if (!password) {
                passwordStrength.style.display = 'none';
                passwordRequirements.style.display = 'none';
                return;
            }

            passwordStrength.style.display = 'block';
            passwordRequirements.style.display = 'block';

            let strength = 0;
            let metRequirements = 0;

            // Check length
            if (requirements.length.test(password)) {
                strength += 20;
                metRequirements++;
                document.getElementById('req-length').classList.add('met');
            } else {
                document.getElementById('req-length').classList.remove('met');
            }

            // Check uppercase
            if (requirements.upper.test(password)) {
                strength += 20;
                metRequirements++;
                document.getElementById('req-upper').classList.add('met');
            } else {
                document.getElementById('req-upper').classList.remove('met');
            }

            // Check lowercase
            if (requirements.lower.test(password)) {
                strength += 20;
                metRequirements++;
                document.getElementById('req-lower').classList.add('met');
            } else {
                document.getElementById('req-lower').classList.remove('met');
            }

            // Check number
            if (requirements.number.test(password)) {
                strength += 20;
                metRequirements++;
                document.getElementById('req-number').classList.add('met');
            } else {
                document.getElementById('req-number').classList.remove('met');
            }

            // Check special character
            if (requirements.special.test(password)) {
                strength += 20;
                metRequirements++;
                document.getElementById('req-special').classList.add('met');
            } else {
                document.getElementById('req-special').classList.remove('met');
            }

            // Determine strength level
            let level = 'weak';
            if (strength >= 80) level = 'strong';
            else if (strength >= 60) level = 'good';
            else if (strength >= 40) level = 'fair';

            // Update strength bar
            strengthBar.className = 'strength-bar ' + level;
            strengthText.className = 'strength-text ' + level;
            strengthText.textContent = `Password Strength: ${level.charAt(0).toUpperCase() + level.slice(1)} (${metRequirements}/5 requirements met)`;

            // Disable submit button if not strong enough
            registerBtn.disabled = strength < 80;
        });

        // Trigger validation on form load if password was filled
        if (passwordInput.value) {
            passwordInput.dispatchEvent(new Event('input'));
        }
    </script>
</body>
</html>
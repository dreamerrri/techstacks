<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Register - HR Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/register.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth-layout">

        <!-- Left: gradient panel (visible on lg+) -->
        <div class="auth-image-panel">
            <div class="auth-image-overlay"></div>
            <div class="auth-image-content">
                <img src="{{ asset('images/techstackfull_ico.png') }}" alt="Techstack Logo" style="width: 130px; margin-bottom: 24px;">
                <h2>LogiPay</h2>
                <p>Streamline your human resources and payroll operations with our comprehensive management platform.</p>
            </div>
        </div>

        <!-- Right: form panel -->
        <div class="auth-form-panel">
            <div class="auth-form-inner">

                <div class="form-header">
                    <h3>Create Account</h3>
                    <p>Join our HR Management System</p>
                </div>

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

                <div class="auth-footer-link">
                    <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                </div>

            </div>
        </div>

    </div>

    <script>
        const passwordInput        = document.getElementById('password');
        const passwordStrength     = document.getElementById('passwordStrength');
        const strengthBar          = document.getElementById('strengthBar');
        const strengthText         = document.getElementById('strengthText');
        const passwordRequirements = document.getElementById('passwordRequirements');
        const registerBtn          = document.getElementById('registerBtn');

        const requirements = {
            length:  /^.{8,}$/,
            upper:   /[A-Z]/,
            lower:   /[a-z]/,
            number:  /[0-9]/,
            special: /[!@#$%^&*()_+\-=\[\]{};:'",.< >?\/\\|`~]/,
        };

        passwordInput.addEventListener('input', function () {
            const password = this.value;

            if (!password) {
                passwordStrength.style.display     = 'none';
                passwordRequirements.style.display = 'none';
                return;
            }

            passwordStrength.style.display     = 'block';
            passwordRequirements.style.display = 'block';

            let strength = 0;
            let metRequirements = 0;

            const checks = ['length', 'upper', 'lower', 'number', 'special'];
            checks.forEach(key => {
                const el = document.getElementById('req-' + key);
                if (requirements[key].test(password)) {
                    strength += 20;
                    metRequirements++;
                    el.classList.add('met');
                } else {
                    el.classList.remove('met');
                }
            });

            let level = 'weak';
            if (strength >= 80)      level = 'strong';
            else if (strength >= 60) level = 'good';
            else if (strength >= 40) level = 'fair';

            strengthBar.className  = 'strength-bar ' + level;
            strengthText.className = 'strength-text ' + level;
            strengthText.textContent = `Password Strength: ${level.charAt(0).toUpperCase() + level.slice(1)} (${metRequirements}/5 requirements met)`;

            registerBtn.disabled = strength < 80;
        });

        if (passwordInput.value) {
            passwordInput.dispatchEvent(new Event('input'));
        }
    </script>
</body>
</html>
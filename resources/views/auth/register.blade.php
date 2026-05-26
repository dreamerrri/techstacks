<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HR Management System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      @vite(['resources/css/register.css', 'resources/js/app.js'])
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

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

         <!-- Left: gradient panel with 3D logo -->
        <div class="auth-image-panel">
            <div class="auth-image-overlay"></div>
            <div class="auth-image-content">

                <!-- 3D Logo Canvas -->
<div id="logo3d-container" style="width: 320px; height: 320px; margin-bottom: 16px;">
    
    <canvas id="logo3d" style="width: 100%; height: 100%;"></canvas>
                </div>
<p style="font-size: 14px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; opacity: 0.75; margin-bottom: 20px;">Techstacks</p>
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
                        <i class="icon-[ph--warning-circle-fill]"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                    @csrf

                    <div class="form-group">
                        <label for="name">
                            <i class="icon-[ph--user-fill]"></i> Full Name
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
                                <i class="icon-[ph--x-circle-fill]"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="icon-[ph--envelope-fill]"></i> Email Address
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
                                <i class="icon-[ph--x-circle-fill]"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="icon-[ph--lock-fill]"></i> Password
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
                                <i class="icon-[ph--circle-fill]"></i>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="requirement" id="req-upper">
                                <i class="icon-[ph--circle-fill]"></i>
                                <span>One uppercase letter (A-Z)</span>
                            </div>
                            <div class="requirement" id="req-lower">
                                <i class="icon-[ph--circle-fill]"></i>
                                <span>One lowercase letter (a-z)</span>
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="icon-[ph--circle-fill]"></i>
                                <span>One number (0-9)</span>
                            </div>
                            <div class="requirement" id="req-special">
                                <i class="icon-[ph--circle-fill]"></i>
                                <span>One special character (!@#$%^&*)</span>
                            </div>
                        </div>

                        @error('password')
                            <div class="error-message">
                                <i class="icon-[ph--x-circle-fill]"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">
                            <i class="icon-[ph--lock-fill]"></i> Confirm Password
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
                                <i class="icon-[ph--x-circle-fill]"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="register-btn" id="registerBtn">
                        <i class="icon-[ph--user-plus-fill]"></i> Create Account
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
                passwordStrength.classList.remove('is-visible');
                passwordRequirements.classList.remove('is-visible');
                return;
            }

            passwordStrength.classList.add('is-visible');
            passwordRequirements.classList.add('is-visible');

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

       <!-- Three.js 3D Logo -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script>
        (function () {
            const container = document.getElementById('logo3d-container');
            const canvas    = document.getElementById('logo3d');

            if (!container || !canvas) return;

            const w = container.clientWidth  || 220;
            const h = container.clientHeight || 220;

            const scene  = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 100);
            camera.position.set(0, 0, 8);

            const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
            renderer.setSize(w, h);
            renderer.setPixelRatio(window.devicePixelRatio);

            // Lights — kept exactly as your logotest.html
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.1);
            scene.add(ambientLight);
            const dirLight = new THREE.DirectionalLight(0xffffff, 3);
            dirLight.position.set(10, 10, 10);
            scene.add(dirLight);

            // OrbitControls — kept exactly as your logotest.html
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.enablePan     = false;
            controls.enableZoom    = false;

            let logo;
            const loader = new THREE.GLTFLoader();
            loader.load(
                '{{ asset("3d/techstacks-logo.gltf") }}',
                function (gltf) {
                    logo = gltf.scene;
                    scene.add(logo);
                },
                undefined,
                function (err) { console.error('GLTF load error:', err); }
            );

            function animate() {
                requestAnimationFrame(animate);
                if (logo) logo.rotation.y += 0.01;
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        })();
    </script>
</body>
</html>
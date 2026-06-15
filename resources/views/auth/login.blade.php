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
                    <h3>Welcome Back</h3>
                    <p>Sign in to access your dashboard</p>
                </div>

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

                    {{-- Submit --}}
                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="fas fa-sign-in-alt" id="loginIcon"></i>
                        <span id="loginText">Sign In</span>
                    </button>
                </form>

                <div class="auth-footer-link">
                    <p>
                        Don't have an account?
                        <a href="{{ route('register') }}">Register here</a>
                    </p>
                </div>

            </div>
        </div>

    </div>

    <script>
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

        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn  = document.getElementById('loginBtn');
            const icon = document.getElementById('loginIcon');
            const text = document.getElementById('loginText');
            btn.disabled     = true;
            icon.className   = 'fas fa-spinner fa-spin';
            text.textContent = ' Signing in...';
        });
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
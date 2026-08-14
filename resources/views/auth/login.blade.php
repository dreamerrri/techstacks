<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
<div id="logo3d-container" class="w-48 h-48 sm:w-64 sm:h-64 lg:w-80 lg:h-80 mb-4">
    
    <canvas id="logo3d" class="w-full h-full"></canvas>
                </div>
<p class="text-sm font-semibold tracking-[2px] uppercase opacity-75 mb-5">Techstacks</p>
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
                        <i class="icon-[ph--warning-circle-fill]"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="icon-[tabler--circle-check]"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate id="loginForm">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email">
                            <i class="icon-[ph--envelope-fill]"></i> Email Address
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
                                <i class="icon-[tabler--circle-x]"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Password with show/hide toggle --}}
                    <div class="form-group">
                        <label for="password">
                            <i class="icon-[ph--lock-fill]"></i> Password
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                class="w-full pe-12"
                            >
                            <button type="button" onclick="togglePassword()"
                                    class="absolute end-3 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-subtle p-0">
                                <i class="icon-[ph--eye-fill]" id="eyeIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="icon-[tabler--circle-x]"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="remember-forgot">
                        <label>
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="{{ route('reset') }}">Forgot password?</a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="icon-[ph--sign-in-fill]" id="loginIcon"></i>
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
icon.className = 'icon-[ph--spinner-fill] spin';
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
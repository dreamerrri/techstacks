<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Reset Password - HR Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/reset.css', 'resources/js/app.js'])
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
                    <h3>Update Password</h3>
                    <p>Enter your new password</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="icon-[ph--warning-circle-fill]"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

<form method="POST" action="{{ route('password.update.submit') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required>
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm New Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>

  
    <button type="submit" class="reset-btn" id="resetBtn">
        <i class="icon-[ph--arrow-clockwise]"></i> Update Password
    </button>
</form>

                <div class="auth-footer-link">
                    <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                </div>

            </div>
        </div>

    </div>



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
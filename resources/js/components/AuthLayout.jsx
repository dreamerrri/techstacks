import { useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
    });
}

export default function AuthLayout({ children, title }) {
    const containerRef = useRef(null);
    const canvasRef = useRef(null);

    useEffect(() => {
        document.body.classList.add('auth-page');
        return () => document.body.classList.remove('auth-page');
    }, []);

    useEffect(() => {
        let renderer, controls, animationId;
        let cancelled = false;

        async function init() {
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js');
            await loadScript('https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js');
            await loadScript('https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js');

            if (cancelled) return;

            const THREE = window.THREE;
            const container = containerRef.current;
            const canvas = canvasRef.current;
            if (!container || !canvas || !THREE) return;

            const w = container.clientWidth || 220;
            const h = container.clientHeight || 220;

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 100);
            camera.position.set(0, 0, 8);

            renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
            renderer.setSize(w, h);
            renderer.setPixelRatio(window.devicePixelRatio);

            scene.add(new THREE.AmbientLight(0xffffff, 0.1));
            const dirLight = new THREE.DirectionalLight(0xffffff, 3);
            dirLight.position.set(10, 10, 10);
            scene.add(dirLight);

            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.enablePan = false;
            controls.enableZoom = false;

            let logo;
            const loader = new THREE.GLTFLoader();
            loader.load(
                '/3d/techstacks-logo.gltf',
                (gltf) => { logo = gltf.scene; scene.add(logo); },
                undefined,
                (err) => console.error('GLTF load error:', err)
            );

            function animate() {
                animationId = requestAnimationFrame(animate);
                if (logo) logo.rotation.y += 0.01;
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        }

        init();

        return () => {
            cancelled = true;
            if (animationId) cancelAnimationFrame(animationId);
            if (controls) controls.dispose();
            if (renderer) renderer.dispose();
        };
    }, []);

    return (
        <>
            <Head title={title} />
            <div className="auth-layout">
                <div className="auth-image-panel">
                    <div className="auth-image-overlay"></div>
                    <div className="auth-image-content">
                        <div ref={containerRef} id="logo3d-container" className="w-48 h-48 sm:w-64 sm:h-64 lg:w-80 lg:h-80 mb-4">
                            <canvas ref={canvasRef} id="logo3d" className="w-full h-full"></canvas>
                        </div>
                        <p className="text-sm font-semibold tracking-[2px] uppercase opacity-75 mb-5">Techstacks</p>
                        <h2>LogiPay</h2>
                        <p>Streamline your human resources and payroll operations with our comprehensive management platform.</p>
                    </div>
                </div>
                <div className="auth-form-panel">
                    <div className="auth-form-inner">{children}</div>
                </div>
            </div>
        </>
    );
}
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
                () => {}
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
            <div className="flex min-h-screen">
                {/* Brand panel */}
                <div
                    className="relative hidden w-[45%] items-center justify-center overflow-hidden lg:flex"
                    style={{ background: 'linear-gradient(to bottom right, #0a2018, #00896a)' }}
                >
                    <div
                        className="pointer-events-none absolute inset-0"
                        style={{
                            background:
                                'linear-gradient(to top, rgba(0, 0, 0, 0.35), transparent 60%)',
                        }}
                    />
                    <div className="relative z-10 flex max-w-md flex-col items-center px-10 text-center text-white">
                        <div ref={containerRef} className="mb-6 h-64 w-64 xl:h-80 xl:w-80">
                            <canvas ref={canvasRef} className="size-full" />
                        </div>
                        <p className="mb-4 text-sm font-semibold uppercase tracking-[3px] text-brand">Techstacks</p>
                        <h2 className="m-0 text-3xl font-bold">LogiPay</h2>
                        <p className="mt-3 leading-relaxed text-dim-foreground">
                            Streamline your human resources and payroll operations with our comprehensive management platform.
                        </p>
                    </div>
                </div>

                {/* Form panel */}
                <div className="flex flex-1 items-center justify-center bg-canvas p-6 sm:p-10">
                    <div className="w-full max-w-md">{children}</div>
                </div>
            </div>
        </>
    );
}

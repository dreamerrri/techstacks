import React, { useState, useEffect, useRef } from 'react';
import { useForm } from '@inertiajs/react';

export default function Login({ status }) {
    const { data, setData, post, errors, processing } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);
    const canvasRef = useRef(null);
    const containerRef = useRef(null);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    useEffect(() => {
        if (!window.THREE || !canvasRef.current || !containerRef.current) return;

        const container = containerRef.current;
        const canvas = canvasRef.current;
        const w = container.clientWidth || 220;
        const h = container.clientHeight || 220;

        const scene = new window.THREE.Scene();
        const camera = new window.THREE.PerspectiveCamera(45, w / h, 0.1, 100);
        camera.position.set(0, 0, 8);

        const renderer = new window.THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
        renderer.setSize(w, h);
        renderer.setPixelRatio(window.devicePixelRatio);

        scene.add(new window.THREE.AmbientLight(0xffffff, 0.1));
        const dirLight = new window.THREE.DirectionalLight(0xffffff, 3);
        dirLight.position.set(10, 10, 10);
        scene.add(dirLight);

        const controls = new window.THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.enablePan = false;
        controls.enableZoom = false;

        let logo;
        let frameId;
        const loader = new window.THREE.GLTFLoader();
        loader.load(
            '/3d/techstacks-logo.gltf',
            (gltf) => {
                logo = gltf.scene;
                scene.add(logo);
            },
            undefined,
            (err) => console.error('GLTF load error:', err)
        );

        const animate = () => {
            frameId = requestAnimationFrame(animate);
            if (logo) logo.rotation.y += 0.01;
            controls.update();
            renderer.render(scene, camera);
        };
        animate();

        return () => cancelAnimationFrame(frameId);
    }, []);

    return (
        <div className="flex w-full h-screen overflow-hidden max-lg:h-auto max-lg:overflow-auto">
            {/* Left gradient panel */}
            <div className="relative w-1/3 shrink-0 overflow-hidden hidden lg:block"
                 style={{ background: 'linear-gradient(to bottom right, #0a2018, #00896a)' }}>
                <div className="absolute inset-0 z-[1]"
                     style={{ background: 'linear-gradient(to top, rgba(0,0,0,0.35), transparent 60%)' }}></div>
                <div className="relative z-[2] flex flex-col items-center justify-center text-center h-full px-10 py-12 text-white">
                    <div ref={containerRef} className="w-48 h-48 sm:w-64 sm:h-64 lg:w-80 lg:h-80 mb-4">
                        <canvas ref={canvasRef} className="w-full h-full"></canvas>
                    </div>
                    <p className="text-sm font-semibold tracking-[2px] uppercase opacity-75 mb-5">Techstacks</p>
                    <h2 className="text-[32px] font-bold mb-4">LogiPay</h2>
                    <p className="text-[15px] leading-[1.7] opacity-90">
                        Streamline your human resources and payroll operations with our comprehensive management platform.
                    </p>
                </div>
            </div>

            {/* Right form panel */}
            <div className="flex-1 bg-white flex items-center justify-center overflow-y-auto px-5 py-10 max-lg:min-h-screen">
                <div className="w-full max-w-[400px]">
                    <div className="mb-8">
                        <h3 className="text-[26px] font-bold text-gray-800 mb-1.5">Welcome Back</h3>
                        <p className="text-sm text-gray-500">Sign in to access your dashboard</p>
                    </div>

                    {Object.keys(errors).length > 0 && (
                        <div className="flex items-center gap-2 px-4 py-3 rounded-md mb-5 text-sm bg-red-100 text-red-800 border-l-4 border-red-600">
                            <i className="icon-[ph--warning-circle-fill]"></i>
                            {Object.values(errors)[0]}
                        </div>
                    )}

                    {status && (
                        <div className="flex items-center gap-2 px-4 py-3 rounded-md mb-5 text-sm bg-green-100 text-green-700 border-l-4 border-green-500">
                            <i className="icon-[tabler--circle-check]"></i>
                            {status}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} noValidate>
                        {/* Email */}
                        <div className="mb-5">
                            <label htmlFor="email" className="block text-gray-700 font-semibold mb-2 text-sm">
                                <i className="icon-[ph--envelope-fill] mr-1"></i> Email Address
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="admin@company.com"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                className="w-full px-[15px] py-3 border-2 border-gray-200 rounded-md text-sm text-gray-800 placeholder-gray-400 transition-colors transition-shadow focus:outline-none focus:border-[#00c896] focus:ring-4 focus:ring-[#00c896]/10"
                            />
                            {errors.email && (
                                <div className="flex items-center gap-1 text-red-600 text-[13px] mt-1.5">
                                    <i className="icon-[tabler--circle-x]"></i>
                                    {errors.email}
                                </div>
                            )}
                        </div>

                        {/* Password */}
                        <div className="mb-5">
                            <label htmlFor="password" className="block text-gray-700 font-semibold mb-2 text-sm">
                                <i className="icon-[ph--lock-fill] mr-1"></i> Password
                            </label>
                            <div className="relative">
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    id="password"
                                    name="password"
                                    placeholder="••••••••"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    required
                                    className="w-full pe-12 px-[15px] py-3 border-2 border-gray-200 rounded-md text-sm text-gray-800 placeholder-gray-400 transition-colors transition-shadow focus:outline-none focus:border-[#00c896] focus:ring-4 focus:ring-[#00c896]/10"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute end-3 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-gray-400 p-0"
                                >
                                    <i className={`icon-[ph--${showPassword ? 'eye-slash' : 'eye'}-fill]`}></i>
                                </button>
                            </div>
                            {errors.password && (
                                <div className="flex items-center gap-1 text-red-600 text-[13px] mt-1.5">
                                    <i className="icon-[tabler--circle-x]"></i>
                                    {errors.password}
                                </div>
                            )}
                        </div>

                        {/* Remember + Forgot */}
                        <div className="flex justify-between items-center mb-7 text-sm">
                            <label className="flex items-center gap-1.5 text-gray-500 font-normal cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                /> Remember me
                            </label>
                            <a href={route('reset')} className="text-[#00c896] font-medium hover:underline">
                                Forgot password?
                            </a>
                        </div>

                        {/* Submit */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3 rounded-md text-white text-[15px] font-semibold transition-all bg-[#00c896] hover:bg-[#00b386] hover:-translate-y-0.5 hover:shadow-[0_5px_20px_rgba(0,200,150,0.35)] active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
                        >
                            <i className={processing ? 'icon-[ph--spinner-fill] animate-spin inline-block mr-1' : 'icon-[ph--sign-in-fill] mr-1'}></i>
                            {processing ? 'Signing in...' : 'Sign In'}
                        </button>
                    </form>

                    <div className="text-center mt-7 text-sm text-gray-500">
                        <p>
                            Don't have an account?{' '}
                            <a href={route('register')} className="text-[#667eea] font-semibold hover:underline">
                                Register here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
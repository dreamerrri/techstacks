import { useEffect } from 'react';
import { Head } from '@inertiajs/react';

export default function AuthLayout({ children, title }) {
    useEffect(() => {
        document.body.classList.add('auth-page');
        return () => document.body.classList.remove('auth-page');
    }, []);

    return (
        <>
            <Head title={title} />
            <div className="auth-layout">
                <div className="auth-image-panel">
                    <div className="auth-image-overlay"></div>
                    <div className="auth-image-content">
                        <div id="logo3d-container" className="w-48 h-48 sm:w-64 sm:h-64 lg:w-80 lg:h-80 mb-4">
                            <canvas id="logo3d" className="w-full h-full"></canvas>
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
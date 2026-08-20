import { useEffect, useRef, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../components/AuthLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });
    const [showPassword, setShowPassword] = useState(false);
    const inputRef = useRef(null);

    useEffect(() => {
        inputRef.current?.focus();
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    const flash = {};

    return (
        <AuthLayout title="Login">
            <div className="form-header">
                <h3>Welcome Back</h3>
                <p>Sign in to access your dashboard</p>
            </div>

            {Object.keys(errors).length > 0 && (
                <div className="alert alert-error">
                    <i className="icon-[ph--warning-circle-fill]"></i>
                    {errors.email || errors.password || Object.values(errors)[0]}
                </div>
            )}

            {flash.success && (
                <div className="alert alert-success">
                    <i className="icon-[tabler--circle-check]"></i>
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit} noValidate id="loginForm">
                <div className="form-group">
                    <label htmlFor="email">
                        <i className="icon-[ph--envelope-fill]"></i> Email Address
                    </label>
                    <input
                        ref={inputRef}
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@company.com"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />
                    {errors.email && (
                        <div className="error-message">
                            <i className="icon-[tabler--circle-x]"></i>
                            {errors.email}
                        </div>
                    )}
                </div>

                <div className="form-group">
                    <label htmlFor="password">
                        <i className="icon-[ph--lock-fill]"></i> Password
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
                            className="w-full pe-12"
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword((v) => !v)}
                            className="absolute end-3 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-subtle p-0"
                        >
                            <i className={showPassword ? 'icon-[ph--eye-slash-fill]' : 'icon-[ph--eye-fill]'}></i>
                        </button>
                    </div>
                    {errors.password && (
                        <div className="error-message">
                            <i className="icon-[tabler--circle-x]"></i>
                            {errors.password}
                        </div>
                    )}
                </div>

                <div className="remember-forgot">
                    <label>
                        <input
                            type="checkbox"
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />{' '}
                        Remember me
                    </label>
                    <Link href="/reset">Forgot password?</Link>
                </div>

                <button type="submit" className="login-btn" id="loginBtn" disabled={processing}>
                    <i className={processing ? 'icon-[ph--spinner-fill] spin' : 'icon-[ph--sign-in-fill]'}></i>
                    <span>{processing ? ' Signing in...' : 'Sign In'}</span>
                </button>
            </form>

            <div className="auth-footer-link">
                <p>
                    Don&apos;t have an account? <Link href="/register">Register here</Link>
                </p>
            </div>
        </AuthLayout>
    );
}
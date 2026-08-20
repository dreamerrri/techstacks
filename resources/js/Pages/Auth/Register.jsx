import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../components/AuthLayout';
import usePasswordStrength from '../../Hooks/usePasswordStrength';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);
    const { strength, requirements } = usePasswordStrength(data.password);

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    const flash = {};

    return (
        <AuthLayout title="Register">
            <div className="form-header">
                <h3>Create Account</h3>
                <p>Join our HR Management System</p>
            </div>

            {Object.keys(errors).length > 0 && !errors.name && !errors.email && !errors.password && !errors.password_confirmation && (
                <div className="alert alert-error">
                    <i className="icon-[ph--warning-circle-fill]"></i>
                    {Object.values(errors)[0]}
                </div>
            )}

            {flash.success && (
                <div className="alert alert-success">
                    <i className="icon-[tabler--circle-check]"></i>
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit} noValidate id="registerForm">
                <div className="form-group">
                    <label htmlFor="name">
                        <i className="icon-[tabler--user]"></i> Full Name
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="John Doe"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && (
                        <div className="error-message">
                            <i className="icon-[tabler--circle-x]"></i>
                            {errors.name}
                        </div>
                    )}
                </div>

                <div className="form-group">
                    <label htmlFor="email">
                        <i className="icon-[ph--envelope-fill]"></i> Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="john@company.com"
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
                            autoComplete="new-password"
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

                    {strength && (
                        <div className="password-strength is-visible" id="passwordStrength">
                            <div className="strength-meter">
                                <div className={`strength-bar ${strength.level <= 1 ? 'weak' : strength.level === 2 ? 'fair' : strength.level === 3 ? 'good' : 'strong'}`} id="strengthBar"></div>
                            </div>
                            <div className={`strength-text ${strength.level <= 1 ? 'weak' : strength.level === 2 ? 'fair' : strength.level === 3 ? 'good' : 'strong'}`} id="strengthText">
                                Password Strength: {strength.label}
                            </div>
                        </div>
                    )}

                    {requirements && (
                        <div className="password-requirements is-visible" id="passwordRequirements">
                            {requirements.map((req) => (
                                <div key={req.label} className={`requirement ${req.met ? 'met' : ''}`}>
                                    <i className={`icon-[ph--${req.met ? 'circle-check-fill' : 'circle-fill'}]`}></i>
                                    <span>{req.label}</span>
                                </div>
                            ))}
                        </div>
                    )}

                    {errors.password && (
                        <div className="error-message">
                            <i className="icon-[tabler--circle-x]"></i>
                            {typeof errors.password === 'string' ? errors.password : (errors.password || []).join(', ')}
                        </div>
                    )}
                </div>

                <div className="form-group">
                    <label htmlFor="password_confirmation">
                        <i className="icon-[ph--lock-fill]"></i> Confirm Password
                    </label>
                    <div className="relative">
                        <input
                            type={showConfirm ? 'text' : 'password'}
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="••••••••"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required
                            autoComplete="new-password"
                            className="w-full pe-12"
                        />
                        <button
                            type="button"
                            onClick={() => setShowConfirm((v) => !v)}
                            className="absolute end-3 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-subtle p-0"
                        >
                            <i className={showConfirm ? 'icon-[ph--eye-slash-fill]' : 'icon-[ph--eye-fill]'}></i>
                        </button>
                    </div>
                    {errors.password_confirmation && (
                        <div className="error-message">
                            <i className="icon-[tabler--circle-x]"></i>
                            {errors.password_confirmation}
                        </div>
                    )}
                </div>

                <button type="submit" className="register-btn" disabled={processing}>
                    <i className={processing ? 'icon-[ph--spinner-fill] spin' : 'icon-[ph--user-plus]'}></i>{' '}
                    {processing ? ' Creating account...' : 'Create Account'}
                </button>
            </form>

            <div className="auth-footer-link">
                <p>
                    Already have an account? <Link href="/login">Login here</Link>
                </p>
            </div>
        </AuthLayout>
    );
}
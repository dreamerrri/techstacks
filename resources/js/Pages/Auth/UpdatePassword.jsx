import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../components/AuthLayout';
import usePasswordStrength from '../../Hooks/usePasswordStrength';

export default function UpdatePassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);
    const { strength, requirements } = usePasswordStrength(data.password);

    const submit = (e) => {
        e.preventDefault();
        post('/password/update/send');
    };

    return (
        <AuthLayout title="Update Password">
            <div className="form-header">
                <h3>Update Password</h3>
                <p>Enter your new password</p>
            </div>

            {Object.keys(errors).length > 0 && !errors.password && !errors.password_confirmation && (
                <div className="alert alert-error">
                    <i className="icon-[ph--warning-circle-fill]"></i>
                    {Object.values(errors)[0]}
                </div>
            )}

            <form onSubmit={submit}>
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
                        <div className="password-strength is-visible">
                            <div className="strength-meter">
                                <div className={`strength-bar ${strength.level <= 1 ? 'weak' : strength.level === 2 ? 'fair' : strength.level === 3 ? 'good' : 'strong'}`}></div>
                            </div>
                            <div className={`strength-text ${strength.level <= 1 ? 'weak' : strength.level === 2 ? 'fair' : strength.level === 3 ? 'good' : 'strong'}`}>
                                Password Strength: {strength.label}
                            </div>
                        </div>
                    )}

                    {requirements && (
                        <div className="password-requirements is-visible">
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

                <button type="submit" className="reset-btn" disabled={processing}>
                    <i className={processing ? 'icon-[ph--spinner-fill] spin' : 'icon-[ph--arrow-clockwise]'}></i>{' '}
                    {processing ? ' Updating...' : 'Update Password'}
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
import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../components/AuthLayout';

export default function Reset() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/password/reset/send');
    };

    const flash = {};

    return (
        <AuthLayout title="Reset Password">
            <div className="form-header">
                <h3>Reset Password</h3>
                <p>Enter your email to reset your password</p>
            </div>

            {Object.keys(errors).length > 0 && (
                <div className="alert alert-error">
                    <i className="icon-[ph--warning-circle-fill]"></i>
                    {errors.email}
                </div>
            )}

            {flash.success && (
                <div className="alert alert-success">
                    <i className="icon-[tabler--circle-check]"></i>
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit}>
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

                <button type="submit" className="reset-btn" disabled={processing}>
                    <i className={processing ? 'icon-[ph--spinner-fill] spin' : 'icon-[ph--arrow-clockwise]'}></i>{' '}
                    {processing ? ' Sending...' : 'Reset Password'}
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
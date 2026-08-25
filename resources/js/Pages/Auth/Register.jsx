import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { CircleAlert, CheckCircle2, Circle, Eye, EyeOff, LoaderCircle, UserPlus } from 'lucide-react';
import AuthLayout from '../../components/AuthLayout';
import usePasswordStrength from '../../Hooks/usePasswordStrength';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

function PasswordInput({ id, value, onChange, error, autoComplete = 'new-password' }) {
    const [show, setShow] = useState(false);
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{id === 'password' ? 'Password' : 'Confirm Password'}</Label>
            <div className="relative">
                <Input
                    type={show ? 'text' : 'password'}
                    id={id}
                    name={id}
                    placeholder="••••••••"
                    value={value}
                    onChange={onChange}
                    required
                    autoComplete={autoComplete}
                    className="pr-10"
                />
                <button
                    type="button"
                    onClick={() => setShow((v) => !v)}
                    aria-label={show ? 'Hide password' : 'Show password'}
                    className="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-0 text-dim-foreground hover:text-canvas-foreground"
                >
                    {show ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                </button>
            </div>
            {error && <p className="m-0 text-xs text-danger">{error}</p>}
        </div>
    );
}

const LEVEL_COLOR = {
    1: 'bg-danger',
    2: 'bg-warning',
    3: 'bg-highlight',
    4: 'bg-brand',
};

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const { strength, requirements } = usePasswordStrength(data.password);

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    const flash = {};
    const fieldErrors = ['name', 'email', 'password', 'password_confirmation'];
    const generalError =
        Object.keys(errors).length > 0 && !fieldErrors.some((k) => errors[k])
            ? Object.values(errors)[0]
            : null;

    return (
        <AuthLayout title="Register">
            <div className="mb-6 text-center">
                <h3 className="m-0 text-xl font-bold">Create Account</h3>
                <p className="mt-1 text-sm text-dim-foreground">Join our HR Management System</p>
            </div>

            {generalError && (
                <div className="mb-4 flex items-center gap-2 rounded-lg border border-danger/40 bg-danger/10 px-3 py-2.5 text-sm text-danger">
                    <CircleAlert className="size-4 shrink-0" />
                    {generalError}
                </div>
            )}

            {flash.success && (
                <div className="mb-4 rounded-lg border border-brand/40 bg-brand/10 px-3 py-2.5 text-sm text-brand">
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit} noValidate id="registerForm" className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="name">Full Name</Label>
                    <Input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="John Doe"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && <p className="m-0 text-xs text-danger">{errors.name}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="email">Email Address</Label>
                    <Input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="john@company.com"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />
                    {errors.email && <p className="m-0 text-xs text-danger">{errors.email}</p>}
                </div>

                <PasswordInput
                    id="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={
                        errors.password &&
                        (typeof errors.password === 'string'
                            ? errors.password
                            : (errors.password || []).join(', '))
                    }
                />

                {strength && (
                    <div className="space-y-1.5">
                        <div className="h-1.5 w-full overflow-hidden rounded-full bg-dim">
                            <div
                                className={`h-full rounded-full transition-all ${LEVEL_COLOR[strength.level] ?? 'bg-dim'}`}
                                style={{ width: `${(strength.level / 4) * 100}%` }}
                            />
                        </div>
                        <p className="m-0 text-xs text-dim-foreground">Password strength: {strength.label}</p>
                        {requirements && (
                            <ul className="m-0 list-none space-y-0.5 p-0">
                                {requirements.map((req) => (
                                    <li key={req.label} className={`flex items-center gap-1.5 text-xs ${req.met ? 'text-brand' : 'text-dim-foreground'}`}>
                                        {req.met ? (
                                            <CheckCircle2 className="size-3 shrink-0" />
                                        ) : (
                                            <Circle className="size-3 shrink-0 opacity-50" />
                                        )}
                                        {req.label}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                )}

                <PasswordInput
                    id="password_confirmation"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    error={errors.password_confirmation}
                />

                <Button type="submit" id="registerFormBtn" disabled={processing} className="w-full">
                    {processing ? <LoaderCircle className="size-4 animate-spin" /> : <UserPlus className="size-4" />}
                    {processing ? 'Creating account…' : 'Create Account'}
                </Button>
            </form>

            <p className="mt-6 mb-0 text-center text-sm text-dim-foreground">
                Already have an account?{' '}
                <Link href="/login" className="font-medium text-brand no-underline hover:underline">
                    Login here
                </Link>
            </p>
        </AuthLayout>
    );
}

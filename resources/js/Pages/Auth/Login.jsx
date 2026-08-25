import { useEffect, useRef, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { CircleAlert, Eye, EyeOff, LoaderCircle, LogIn } from 'lucide-react';
import AuthLayout from '../../components/AuthLayout';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
            <div className="mb-6 text-center">
                <h3 className="m-0 text-xl font-bold">Welcome Back</h3>
                <p className="mt-1 text-sm text-dim-foreground">Sign in to access your dashboard</p>
            </div>

            {Object.keys(errors).length > 0 && (
                <div className="mb-4 flex items-center gap-2 rounded-lg border border-danger/40 bg-danger/10 px-3 py-2.5 text-sm text-danger">
                    <CircleAlert className="size-4 shrink-0" />
                    {errors.email || errors.password || Object.values(errors)[0]}
                </div>
            )}

            {flash.success && (
                <div className="mb-4 flex items-center gap-2 rounded-lg border border-brand/40 bg-brand/10 px-3 py-2.5 text-sm text-brand">
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit} noValidate id="loginForm" className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="email">Email Address</Label>
                    <Input
                        ref={inputRef}
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@company.com"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoFocus
                    />
                    {errors.email && (
                        <p className="m-0 text-xs text-danger">{errors.email}</p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password">Password</Label>
                    <div className="relative">
                        <Input
                            type={showPassword ? 'text' : 'password'}
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            className="pr-10"
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword((v) => !v)}
                            aria-label={showPassword ? 'Hide password' : 'Show password'}
                            className="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-0 text-dim-foreground hover:text-canvas-foreground"
                        >
                            {showPassword ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                        </button>
                    </div>
                    {errors.password && (
                        <p className="m-0 text-xs text-danger">{errors.password}</p>
                    )}
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex cursor-pointer items-center gap-2 text-sm">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onCheckedChange={(checked) => setData('remember', !!checked)}
                        />
                        Remember me
                    </label>
                    <Link href="/reset" className="text-sm font-medium text-brand no-underline hover:underline">
                        Forgot password?
                    </Link>
                </div>

                <Button type="submit" id="loginBtn" disabled={processing} className="w-full">
                    {processing ? <LoaderCircle className="size-4 animate-spin" /> : <LogIn className="size-4" />}
                    {processing ? 'Signing in…' : 'Sign In'}
                </Button>
            </form>

            <p className="mt-6 mb-0 text-center text-sm text-dim-foreground">
                Don&apos;t have an account?{' '}
                <Link href="/register" className="font-medium text-brand no-underline hover:underline">
                    Register here
                </Link>
            </p>
        </AuthLayout>
    );
}

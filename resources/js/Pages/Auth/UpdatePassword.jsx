import { Link, useForm } from '@inertiajs/react';
import { CircleAlert, LoaderCircle, Lock } from 'lucide-react';
import AuthLayout from '../../components/AuthLayout';
import usePasswordStrength from '../../Hooks/usePasswordStrength';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function UpdatePassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });
    const { strength, requirements } = usePasswordStrength(data.password);

    const submit = (e) => {
        e.preventDefault();
        post('/password/update/send');
    };

    const generalError =
        Object.keys(errors).length > 0 && !errors.password && !errors.password_confirmation
            ? Object.values(errors)[0]
            : null;

    return (
        <AuthLayout title="Update Password">
            <div className="mb-6 text-center">
                <h3 className="m-0 text-xl font-bold">Update Password</h3>
                <p className="mt-1 text-sm text-dim-foreground">Enter your new password</p>
            </div>

            {generalError && (
                <div className="mb-4 flex items-center gap-2 rounded-lg border border-danger/40 bg-danger/10 px-3 py-2.5 text-sm text-danger">
                    <CircleAlert className="size-4 shrink-0" />
                    {generalError}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="password">Password</Label>
                    <Input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    {errors.password && <p className="m-0 text-xs text-danger">{errors.password}</p>}
                </div>

                {strength && (
                    <div className="space-y-1.5">
                        <div className="h-1.5 w-full overflow-hidden rounded-full bg-dim">
                            <div
                                className={`h-full rounded-full transition-all ${
                                    strength.level <= 1
                                        ? 'bg-danger'
                                        : strength.level === 2
                                          ? 'bg-warning'
                                          : strength.level === 3
                                            ? 'bg-highlight'
                                            : 'bg-brand'
                                }`}
                                style={{ width: `${(strength.level / 4) * 100}%` }}
                            />
                        </div>
                        {requirements && (
                            <ul className="m-0 list-none space-y-0.5 p-0">
                                {requirements.map((req) => (
                                    <li key={req.label} className={`flex items-center gap-1.5 text-xs ${req.met ? 'text-brand' : 'text-dim-foreground'}`}>
                                        • {req.label}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                )}

                <div className="space-y-2">
                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                    <Input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="••••••••"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    {errors.password_confirmation && (
                        <p className="m-0 text-xs text-danger">{errors.password_confirmation}</p>
                    )}
                </div>

                <Button type="submit" disabled={processing} className="w-full">
                    {processing ? <LoaderCircle className="size-4 animate-spin" /> : <Lock className="size-4" />}
                    {processing ? 'Updating…' : 'Update Password'}
                </Button>
            </form>

            <p className="mt-6 mb-0 text-center text-sm text-dim-foreground">
                Remembered it?{' '}
                <Link href="/login" className="font-medium text-brand no-underline hover:underline">
                    Login here
                </Link>
            </p>
        </AuthLayout>
    );
}

import { Link, useForm } from '@inertiajs/react';
import { CircleAlert, LoaderCircle, Mail } from 'lucide-react';
import AuthLayout from '../../components/AuthLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
            <div className="mb-6 text-center">
                <h3 className="m-0 text-xl font-bold">Reset Password</h3>
                <p className="mt-1 text-sm text-dim-foreground">Enter your email to reset your password</p>
            </div>

            {Object.keys(errors).length > 0 && (
                <div className="mb-4 flex items-center gap-2 rounded-lg border border-danger/40 bg-danger/10 px-3 py-2.5 text-sm text-danger">
                    <CircleAlert className="size-4 shrink-0" />
                    {errors.email}
                </div>
            )}

            {flash.success && (
                <div className="mb-4 rounded-lg border border-brand/40 bg-brand/10 px-3 py-2.5 text-sm text-brand">
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
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

                <Button type="submit" disabled={processing} className="w-full">
                    {processing ? <LoaderCircle className="size-4 animate-spin" /> : <Mail className="size-4" />}
                    {processing ? 'Sending…' : 'Send Reset Link'}
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

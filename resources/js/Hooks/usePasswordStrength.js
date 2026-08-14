import { useMemo } from 'react';

export default function usePasswordStrength(password = '') {
    const strength = useMemo(() => {
        if (!password) return null;
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;

        const label = ['Weak', 'Fair', 'Good', 'Strong'][score - 1] ?? 'Weak';
        const level = score;
        return { score, label, level };
    }, [password]);

    const requirements = useMemo(() => {
        if (!password) return null;
        return [
            { label: 'At least 8 characters', met: password.length >= 8 },
            { label: 'One uppercase letter (A-Z)', met: /[A-Z]/.test(password) },
            { label: 'One lowercase letter (a-z)', met: /[a-z]/.test(password) },
            { label: 'One number (0-9)', met: /\d/.test(password) },
            { label: 'One special character (!@#$%^&*)', met: /[!@#$%^&*(),.?":{}|<>]/.test(password) },
        ];
    }, [password]);

    return { strength, requirements };
}
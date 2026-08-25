import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import Icon from './Icon';
import { cn } from '@/lib/utils';

const THEMES = [
    { id: 'techstacks', label: 'Techstacks Dark' },
    { id: 'techstacks-light', label: 'Techstacks Light' },
];

export default function ThemeDropdown({ label = 'Theme' }) {
    const [selectedTheme, setSelectedTheme] = useState(
        () => document.documentElement.dataset.theme || 'techstacks'
    );

    const selectTheme = (theme) => {
        setSelectedTheme(theme);
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        fetch('/settings/theme', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ theme }),
        }).catch(() => {});
    };

    return (
        <div className={cn('flex items-center gap-1.5 rounded-full border border-edge bg-card px-3 py-1.5')}>
            <Icon name="tabler--palette" className="size-4 text-brand" />
            <select
                value={selectedTheme}
                onChange={(e) => selectTheme(e.target.value)}
                aria-label={`${label} switcher`}
                className="cursor-pointer appearance-none bg-transparent text-xs font-medium text-canvas-foreground outline-none"
            >
                {THEMES.map((theme) => (
                    <option key={theme.id} value={theme.id} className="bg-card text-canvas-foreground">
                        {theme.label}
                    </option>
                ))}
            </select>
            <Icon name="tabler--chevron-down" className="size-3.5 opacity-60" />
        </div>
    );
}

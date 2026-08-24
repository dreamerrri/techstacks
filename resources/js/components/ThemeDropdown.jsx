import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import Icon from './Icon';

const THEMES = [
    'techstacks', 'techstacks-light', 'light', 'dark', 'black', 'claude', 'corporate',
    'ghibli', 'gourmet', 'luxury', 'mintlify', 'pastel', 'perplexity', 'shadcn',
    'slack', 'soft', 'spotify', 'valorant', 'vscode',
];

export default function ThemeDropdown({ label = 'Theme' }) {
    const { props } = usePage();
    const currentTheme = props.auth?.user?.theme || 'light';
    const [open, setOpen] = useState(false);
    const [selectedTheme, setSelectedTheme] = useState(currentTheme);

    const selectTheme = (theme) => {
        setSelectedTheme(theme);
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        setOpen(false);
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
        <div className={`dropdown relative inline-flex [--auto-close:inside] ${open ? 'open' : ''}`}>
            <button
                type="button"
                className="dropdown-toggle btn btn-soft btn-primary btn-sm rounded-full gap-2"
                aria-haspopup="menu"
                aria-expanded={open}
                aria-label={`${label} switcher`}
                onClick={() => setOpen((v) => !v)}
            >
                {label}
                <span className={`icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform`}></span>
            </button>

            {open && (
                <div className="dropdown-menu dropdown-open:opacity-100 w-64 p-2" role="menu" aria-orientation="vertical">
                    <div className="theme-scroll max-h-80 overflow-y-auto overflow-x-hidden py-2 pr-2 flex flex-col gap-2">
                        {THEMES.map((theme) => (
                            <label
                                key={theme}
                                data-theme={theme}
                                className="theme-row group w-full box-border flex items-center justify-between rounded-full border-2 border-transparent bg-base-100 text-base-content px-4 py-2 cursor-pointer transition-all duration-150 hover:border-base-300 has-checked:border-primary"
                            >
                                <span className="flex items-center gap-2 min-w-0">
                                    <input
                                        type="radio"
                                        name="theme-picker-react"
                                        value={theme}
                                        className="theme-picker-input sr-only"
                                        checked={selectedTheme === theme}
                                        onChange={() => selectTheme(theme)}
                                    />
                                    <span className="icon-[tabler--check] size-4 shrink-0 opacity-0 transition-opacity group-has-checked:opacity-100"></span>
                                    <span className="capitalize truncate font-medium">{theme}</span>
                                </span>
                                <span className="ml-4 flex items-center gap-1 shrink-0">
                                    <span className="h-5 w-2 rounded-sm bg-primary"></span>
                                    <span className="h-5 w-2 rounded-sm bg-secondary"></span>
                                    <span className="h-5 w-2 rounded-sm bg-accent"></span>
                                </span>
                            </label>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
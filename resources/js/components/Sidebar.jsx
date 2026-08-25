import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import NAV from '../Config/nav';
import { cn } from '@/lib/utils';

function isRouteActive(patterns, currentUrl) {
    return patterns.some((p) => currentUrl.startsWith(`/${p}`) || currentUrl === `/${p}`);
}

function Brand({ minified, onClick }) {
    return (
        <Link href="/dashboard" onClick={onClick} className="flex h-16 shrink-0 items-center gap-2.5 border-b border-edge px-4 no-underline">
            <svg fill="currentColor" viewBox="0 0 1813 1441" className="size-8 shrink-0 text-brand">
                <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fillRule="evenodd" />
                <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fillRule="evenodd" />
            </svg>
            {!minified && (
                <div className="leading-tight">
                    <span className="block text-base font-semibold tracking-wide">Techstacks</span>
                    <span className="block text-xs text-dim-foreground">Logify</span>
                </div>
            )}
        </Link>
    );
}

export default function Sidebar({ open, onClose, minified }) {
    const { url } = usePage();
    const role = usePage().props.role ?? 'user';
    const nav = NAV[role] ?? NAV.user;
    const currentUrl = url.split('?')[0];
    const [openGroups, setOpenGroups] = useState({});

    useEffect(() => {
        setOpenGroups((prev) => {
            const next = { ...prev };
            nav.groups.forEach((group) => {
                if (group.items.some((item) => isRouteActive(item.active, currentUrl))) next[group.label] = true;
            });
            return next;
        });
    }, [currentUrl]);

    useEffect(() => {
        if (!open) return;
        const onKey = (e) => e.key === 'Escape' && onClose();
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [open, onClose]);

    const toggleGroup = (label) =>
        setOpenGroups((prev) => ({ ...prev, [label]: !prev[label] }));

    const itemClass = (item) =>
        cn(
            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium no-underline transition-colors',
            isRouteActive(item.active, currentUrl)
                ? 'bg-brand/10 font-semibold text-brand'
                : 'text-dim-foreground hover:bg-dim hover:text-canvas-foreground'
        );

    const ExpandedNav = (
        <nav className="flex-1 space-y-4 overflow-y-auto overflow-x-hidden p-3" aria-label="Primary">
            <div className="space-y-1">
                <Link href="/dashboard" onClick={onClose} className={cn(itemClass({ active: ['dashboard'] }), !isRouteActive(['dashboard'], currentUrl) && 'text-dim-foreground')}>
                    <span className="icon-[tabler--home] size-5" />
                    Dashboard
                </Link>
            </div>

            {nav.groups.map((group) => {
                const isOpen = !!openGroups[group.label];
                return (
                    <div key={group.label}>
                        <button
                            type="button"
                            onClick={() => toggleGroup(group.label)}
                            aria-expanded={isOpen}
                            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-dim-foreground transition-colors hover:text-canvas-foreground"
                        >
                            <span className={`icon-[${group.icon}] size-5`} />
                            {group.label}
                            <ChevronDown className={cn('ml-auto size-4 opacity-60 transition-transform', isOpen && 'rotate-180')} />
                        </button>
                        {isOpen && (
                            <ul className="mt-1 space-y-1 border-l border-edge pl-3">
                                {group.items.map((item) => (
                                    <li key={item.title}>
                                        <Link href={item.href} onClick={onClose} className={itemClass(item)}>
                                            <span className={`icon-[${item.icon}] size-5`} />
                                            {item.title}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                );
            })}
        </nav>
    );

    const CollapsedNav = (
        <nav className="flex flex-1 flex-col items-center gap-2 overflow-y-auto p-2" aria-label="Primary (collapsed)">
            {nav.flat.map((item) => (
                <Link
                    key={item.title}
                    href={item.href}
                    onClick={onClose}
                    title={item.title}
                    aria-label={item.title}
                    className={cn(
                        'flex size-10 items-center justify-center rounded-lg transition-colors',
                        isRouteActive(item.active, currentUrl)
                            ? 'bg-brand/10 text-brand'
                            : 'text-dim-foreground hover:bg-dim hover:text-canvas-foreground'
                    )}
                >
                    <span className={`icon-[${item.icon}] size-5`} />
                </Link>
            ))}
        </nav>
    );

    return (
        <>
            {/* Mobile backdrop */}
            <div
                className={cn(
                    'fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm transition-opacity duration-300 sm:hidden',
                    open ? 'opacity-100' : 'pointer-events-none opacity-0'
                )}
                onClick={onClose}
                aria-hidden="true"
            />

            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-[70] flex w-60 flex-col border-r border-edge bg-card transition-transform duration-300',
                    'max-sm:w-64',
                    open ? 'max-sm:translate-x-0 max-sm:shadow-2xl' : 'max-sm:-translate-x-full rtl:max-sm:translate-x-full',
                    minified ? 'sm:w-[var(--sidebar-w-mini)]' : 'sm:w-[var(--sidebar-w)]',
                    'sm:translate-x-0 rtl:sm:translate-x-0'
                )}
                role="dialog"
                aria-label="Sidebar"
            >
                <Brand minified={minified} onClick={() => {}} />
                {minified ? CollapsedNav : ExpandedNav}
            </aside>
        </>
    );
}

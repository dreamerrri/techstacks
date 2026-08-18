import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Icon from './Icon';
import NAV from '../Config/nav';

function isRouteActive(patterns, currentUrl) {
    return patterns.some((p) => currentUrl.startsWith(`/${p}`) || currentUrl === `/${p}`);
}

export default function Sidebar({ open, onClose, minified }) {
    const { url } = usePage();
    const role = usePage().props.role ?? 'user';
    const nav = NAV[role] ?? NAV.user;

    const currentUrl = url.split('?')[0];
    const [openGroups, setOpenGroups] = useState({});

    // Auto-open groups containing the current route.
    useEffect(() => {
        const next = {};
        nav.groups.forEach((group) => {
            const anyActive = group.items.some((item) => isRouteActive(item.active, currentUrl));
            if (anyActive) next[group.label] = true;
        });
        setOpenGroups((prev) => {
            const merged = { ...prev };
            Object.entries(next).forEach(([k, v]) => (merged[k] = v));
            return merged;
        });
    }, [currentUrl]);

    const toggleGroup = (label) =>
        setOpenGroups((prev) => {
            const next = {};
            Object.keys(prev).forEach((k) => {
                if (k !== label && prev[k]) next[k] = false;
            });
            next[label] = !prev[label];
            return next;
        });

    const header = (
        <div className={`drawer-header py-2 w-full flex items-center ${minified ? 'justify-center' : ''}`}>
            <Link href="/dashboard" className="techicon flex items-center gap-2 no-underline" onClick={onClose}>
                <svg fill="currentColor" height="2em" viewBox="0 0 1813 1441" width="2em" xmlns="http://www.w3.org/2000/svg" className="brand-logo-icon shrink-0 text-primary">
                    <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fillRule="evenodd"></path>
                    <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fillRule="evenodd"></path>
                </svg>
                {!minified && (
                    <div className="tech drawer-title tracking-wide">
                        <span className="block text-xl font-semibold text-primary">Techstacks</span>
                        <span className="block text-xs text-primary/60">
                            {role === 'admin' ? 'Admin Portal' : role === 'hr' ? 'HR Portal' : 'Employee Portal'}
                        </span>
                    </div>
                )}
            </Link>
        </div>
    );

    const navLinkClass = (item) =>
        `tooltip-toggle ${isRouteActive(item.active, currentUrl) ? 'active' : ''}`;

    const ExpandedNav = (
        <nav className="drawer-body px-2 pt-4" aria-label="Primary">
            <ul className="menu p-0">
                <li>
                    <Link href="/dashboard" className={currentUrl === '/dashboard' ? 'active' : ''} onClick={onClose}>
                        <span className="icon-[tabler--home] size-5"></span>
                        <span>Dashboard</span>
                    </Link>
                </li>

                {nav.groups.map((group) => {
                    const isOpen = !!openGroups[group.label];
                    const groupActive = group.items.some((item) => isRouteActive(item.active, currentUrl));
                    return (
                        <li key={group.label} className={`dropdown relative [--strategy:static] --prevent-on-load-init ${isOpen ? 'open' : ''}`}>
                            <button
                                type="button"
                                className="dropdown-toggle"
                                aria-haspopup="menu"
                                aria-expanded={isOpen}
                                onClick={() => toggleGroup(group.label)}
                            >
                                <span className={`icon-[${group.icon}] size-5`}></span>
                                <span>{group.label}</span>
                                <span className={`icon-[tabler--chevron-down] size-4 transition-transform ${isOpen ? 'rotate-180' : ''}`}></span>
                            </button>
                            {isOpen && (
                                <ul className={`dropdown-menu dropdown-open:opacity-100 mt-0 shadow-none min-w-40 ms-6 ps-2 border-s border-base-content/20 rounded-none`} role="menu">
                                    {group.items.map((item) => (
                                        <li key={item.title}>
                                            <Link href={item.href} className={navLinkClass(item)} onClick={onClose}>
                                                <span className={`icon-[${item.icon}] size-5`}></span>
                                                <span className="text-md">{item.title}</span>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </li>
                    );
                })}
            </ul>
        </nav>
    );

    const CollapsedNav = (
        <nav className="drawer-body px-2 pt-4" aria-label="Primary (collapsed)">
            <ul className="menu p-0 items-center gap-1">
                {nav.flat.map((item) => (
                    <li key={item.title} className="tooltip [--placement:right]">
                        <Link href={item.href} className={navLinkClass(item)} aria-label={item.title} onClick={onClose}>
                            <span className={`icon-[${item.icon}] size-5`}></span>
                        </Link>
                        <span className="tooltip-content tooltip-shown:opacity-100 z-999 tooltip-shown:visible" role="tooltip">
                            <span className="tooltip-body">{item.title}</span>
                        </span>
                    </li>
                ))}
            </ul>
        </nav>
    );

    return (
        <aside
            id="collapsible-mini-sidebar"
            className={[
                'overlay [--auto-close:sm] border-r border-base-300 drawer drawer-start',
                'hidden sm:fixed sm:inset-y-0 sm:start-0 sm:z-10 sm:flex sm:translate-x-0',
                open ? 'open translate-x-0' : '',
                'w-[var(--sidebar-w)]',
                minified ? 'minified w-[var(--sidebar-w-mini)]' : '',
            ].join(' ')}
            role="dialog"
            tabIndex="-1"
        >
            {header}
            {minified ? CollapsedNav : ExpandedNav}
        </aside>
    );
}
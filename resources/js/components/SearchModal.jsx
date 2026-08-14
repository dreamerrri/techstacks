import { useEffect, useMemo, useRef, useState } from 'react';
import Icon from './Icon';

const PAGES = {
    admin: [
        { title: 'Dashboard', icon: 'tabler--home', url: '/dashboard', keywords: 'home' },
        { title: 'Users', icon: 'tabler--users', url: '/users', keywords: 'accounts' },
        { title: 'Roles', icon: 'tabler--shield', url: '/roles', keywords: '' },
        { title: 'Permissions', icon: 'tabler--shield-check', url: '/permissions', keywords: '' },
        { title: 'Employees', icon: 'tabler--id', url: '/employees', keywords: 'staff' },
        { title: 'Attendance', icon: 'tabler--calendar-check', url: '/manual-payroll-attendance', keywords: '' },
        { title: 'Work Requests', icon: 'tabler--notes', url: '/work-requests', keywords: '' },
        { title: 'Payroll', icon: 'tabler--cash', url: '/payroll', keywords: '' },
        { title: 'Contributions', icon: 'tabler--id-badge', url: '/government-contributions', keywords: 'sss philhealth pagibig' },
        { title: 'Audit Logs', icon: 'tabler--file-text', url: '/audit-logs', keywords: '' },
    ],
    hr: [
        { title: 'Dashboard', icon: 'tabler--home', url: '/dashboard', keywords: 'home' },
        { title: 'Employees', icon: 'tabler--id', url: '/employees', keywords: 'staff' },
        { title: 'Attendance', icon: 'tabler--calendar-check', url: '/manual-payroll-attendance', keywords: '' },
        { title: 'Work Requests', icon: 'tabler--notes', url: '/work-requests', keywords: '' },
        { title: 'Payroll', icon: 'tabler--cash', url: '/payroll', keywords: '' },
        { title: 'Contributions', icon: 'tabler--id-badge', url: '/government-contributions', keywords: 'sss philhealth pagibig' },
    ],
    user: [
        { title: 'Dashboard', icon: 'tabler--home', url: '/dashboard', keywords: 'home' },
        { title: 'My Profile', icon: 'tabler--user', url: '/profile', keywords: '' },
        { title: 'My Payslip', icon: 'tabler--receipt', url: '/payroll', keywords: '' },
        { title: 'Attendance', icon: 'tabler--clock', url: '/employee-attendance', keywords: '' },
    ],
};

export default function SearchModal({ open, onClose }) {
    const role = document.body.dataset.role || 'user';
    const [query, setQuery] = useState('');
    const [activeIndex, setActiveIndex] = useState(0);
    const [loading, setLoading] = useState(false);
    const [recordGroups, setRecordGroups] = useState([]);
    const inputRef = useRef(null);
    const debounceRef = useRef(null);

    useEffect(() => {
        if (open) {
            setQuery('');
            setRecordGroups([]);
            setActiveIndex(0);
            setTimeout(() => inputRef.current?.focus(), 100);
        }
    }, [open]);

    useEffect(() => {
        if (!open) return;
        const onKey = (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                onClose();
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [open]);

    useEffect(() => {
        if (!open) return;
        const onKeyDown = (e) => {
            if (e.key === 'Escape') onClose();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActiveIndex((i) => (flatResults.length ? (i + 1) % flatResults.length : 0));
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActiveIndex((i) => (flatResults.length ? (i - 1 + flatResults.length) % flatResults.length : 0));
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                select(activeIndex);
            }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [open, query, recordGroups, activeIndex]);

    const pages = PAGES[role] ?? PAGES.user;

    const pageResults = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) return pages.slice(0, 8);
        return pages
            .filter((p) => p.title.toLowerCase().includes(q) || (p.keywords || '').toLowerCase().includes(q))
            .slice(0, 6);
    }, [query, pages]);

    const flatResults = useMemo(() => {
        const items = pageResults.map((p) => ({ ...p, group: 'Pages' }));
        recordGroups.forEach((g) => g.items.forEach((i) => items.push({ ...i, group: g.label })));
        return items;
    }, [pageResults, recordGroups]);

    useEffect(() => {
        if (!open) return;
        if (query.trim().length < 2) {
            setRecordGroups([]);
            return;
        }
        clearTimeout(debounceRef.current);
        setLoading(true);
        debounceRef.current = setTimeout(() => {
            fetch('/search?q=' + encodeURIComponent(query))
                .then((r) => r.json())
                .then((data) => setRecordGroups(data.groups ?? []))
                .catch(() => setRecordGroups([]))
                .finally(() => setLoading(false));
        }, 250);
    }, [query, open]);

    const select = (index) => {
        const item = flatResults[index];
        if (item) window.location.href = item.url;
    };

    if (!open) return null;

    return (
        <div id="search-modal" className="overlay modal overlay-open:opacity-100 overlay-open:duration-300" role="dialog" tabIndex="-1" onClick={onClose}>
            <div className="modal-dialog overflow-x-hidden" onClick={(e) => e.stopPropagation()}>
                <div className="modal-content max-h-full">
                    <div className="modal-header block">
                        <div className="relative">
                            <input
                                ref={inputRef}
                                type="text"
                                className="input ps-8"
                                placeholder="Search or type a command"
                                value={query}
                                onChange={(e) => {
                                    setQuery(e.target.value);
                                    setActiveIndex(0);
                                }}
                            />
                            <Icon name="tabler--search" className="text-base-content absolute start-3 top-1/2 size-4 shrink-0 -translate-y-1/2" />
                        </div>
                    </div>
                    <div className="modal-body">
                        <div className="overflow-y-auto max-h-72 space-y-0.5">
                            {flatResults.map((item, index) => (
                                <div key={item.group + '-' + item.url}>
                                    {(index === 0 || flatResults[index - 1].group !== item.group) && (
                                        <div className="px-2 pt-2 pb-1 text-xs font-semibold uppercase text-subtle">{item.group}</div>
                                    )}
                                    <button
                                        type="button"
                                        className={`flex w-full items-center gap-2 rounded-lg px-2 py-2 text-start text-sm ${index === activeIndex ? 'bg-base-200' : 'hover:bg-base-200'}`}
                                        onClick={() => select(index)}
                                        onMouseEnter={() => setActiveIndex(index)}
                                    >
                                        <Icon name={item.icon} className="size-4 shrink-0 text-subtle" />
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate">{item.title}</span>
                                            {item.subtitle && <span className="block truncate text-xs text-subtle">{item.subtitle}</span>}
                                        </span>
                                    </button>
                                </div>
                            ))}

                            {loading && (
                                <div className="flex items-center justify-center py-4">
                                    <span className="loading loading-spinner loading-sm text-subtle"></span>
                                </div>
                            )}

                            {!loading && query.trim().length > 0 && flatResults.length === 0 && (
                                <div className="px-2 py-6 text-center text-sm text-subtle">
                                    No results for &quot;{query}&quot;
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
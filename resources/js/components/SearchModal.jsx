import { useEffect, useMemo, useRef, useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { Search } from 'lucide-react';
import { cn } from '@/lib/utils';

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
            setTimeout(() => inputRef.current?.focus(), 50);
        }
    }, [open]);

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

    // Debounced record search with abort on change/close
    useEffect(() => {
        if (!open) return;
        const controller = new AbortController();
        if (query.trim().length < 2) {
            setRecordGroups([]);
            setLoading(false);
            return () => controller.abort();
        }
        clearTimeout(debounceRef.current);
        setLoading(true);
        debounceRef.current = setTimeout(() => {
            fetch('/search?q=' + encodeURIComponent(query), { signal: controller.signal })
                .then((r) => r.json())
                .then((data) => setRecordGroups(data.groups ?? []))
                .catch((err) => {
                    if (err.name !== 'AbortError') setRecordGroups([]);
                })
                .finally(() => {
                    if (!controller.signal.aborted) setLoading(false);
                });
        }, 250);
        return () => {
            clearTimeout(debounceRef.current);
            controller.abort();
        };
    }, [query, open]);

    const select = (index) => {
        const item = flatResults[index];
        if (item) window.location.href = item.url;
    };

    return (
        <Dialog open={open} onOpenChange={(v) => !v && onClose()}>
            <DialogContent className="top-[15%] max-w-lg translate-y-0 gap-0 overflow-hidden p-0">
                <DialogTitle className="sr-only">Search</DialogTitle>
                <DialogDescription className="sr-only">Search pages and records</DialogDescription>

                <div className="relative border-b border-edge">
                    <Search className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-dim-foreground" />
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => {
                            setQuery(e.target.value);
                            setActiveIndex(0);
                        }}
                        placeholder="Search pages and records…"
                        className="h-12 w-full bg-transparent pl-10 pr-4 text-sm outline-none placeholder:text-dim-foreground"
                    />
                </div>

                <div className="max-h-72 overflow-y-auto p-2">
                    {flatResults.map((item, index) => (
                        <div key={item.group + '-' + item.url}>
                            {(index === 0 || flatResults[index - 1].group !== item.group) && (
                                <div className="px-2 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-dim-foreground">
                                    {item.group}
                                </div>
                            )}
                            <button
                                type="button"
                                onClick={() => select(index)}
                                onMouseEnter={() => setActiveIndex(index)}
                                className={cn(
                                    'flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-start text-sm',
                                    index === activeIndex ? 'bg-brand/10 text-brand' : 'hover:bg-dim'
                                )}
                            >
                                <span className={`icon-[${item.icon}] size-4 shrink-0 ${index === activeIndex ? 'text-brand' : 'text-dim-foreground'}`} />
                                <span className="min-w-0 flex-1 truncate">{item.title}</span>
                                {item.subtitle && (
                                    <span className="truncate text-xs text-dim-foreground">{item.subtitle}</span>
                                )}
                            </button>
                        </div>
                    ))}

                    {loading && (
                        <div className="flex items-center justify-center py-6">
                            <span className="size-5 animate-spin rounded-full border-2 border-edge border-t-brand" />
                        </div>
                    )}

                    {!loading && query.trim().length > 0 && flatResults.length === 0 && (
                        <p className="px-2 py-8 text-center text-sm text-dim-foreground">
                            No results for &ldquo;{query}&rdquo;
                        </p>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}

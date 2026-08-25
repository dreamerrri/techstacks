import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import Pagination from './Pagination';
import { cn } from '@/lib/utils';

export default function DataTable({
    title,
    icon,
    tooltip,
    actions,
    search = false,
    searchPlaceholder = 'Search...',
    filters = [],
    baseUrl,
    paginator,
    empty,
    children,
}) {
    const { url } = usePage();
    const searchTimerRef = useRef(null);
    const [searchValue, setSearchValue] = useState(() => {
        const params = new URLSearchParams(url.split('?')[1] || '');
        return params.get('search') || '';
    });

    const navigateWithParams = (mutate) => {
        const path = baseUrl.split('?')[0];
        const params = new URLSearchParams(window.location.search);
        mutate(params);
        params.delete('page');
        router.get(path, Object.fromEntries(params), {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    const submitSearch = (value) => {
        clearTimeout(searchTimerRef.current);
        searchTimerRef.current = setTimeout(() => {
            navigateWithParams((params) => {
                if (value) params.set('search', value);
                else params.delete('search');
            });
        }, 400);
    };

    const handleSearch = (value) => {
        setSearchValue(value);
        submitSearch(value);
    };

    const handleFilter = (name, value) => {
        clearTimeout(searchTimerRef.current);
        navigateWithParams((params) => {
            if (value) params.set(name, value);
            else params.delete(name);
        });
    };

    const hasActiveFilters = () => {
        const params = new URLSearchParams(url.split('?')[1] || '');
        return [...params.keys()].some((k) => ['search', 'per_page', ...filters.map((f) => f.name)].includes(k));
    };

    useEffect(() => () => clearTimeout(searchTimerRef.current), []);

    return (
        <div className="flex w-full min-w-0 flex-col rounded-xl border border-edge bg-card">
            <div className="flex flex-wrap items-center justify-between gap-3 px-5 pt-5 pb-4">
                <h2 className="m-0 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-dim-foreground">
                    {icon && <span className={`icon-[${icon}] size-4 text-brand`} />}
                    <span>{title}</span>
                    {tooltip && (
                        <span className="tooltip [--placement:right] cursor-help" tabIndex={0}>
                            <span className="tooltip-toggle text-dim-foreground/70">
                                <span className="icon-[tabler--info-circle] size-3.5" />
                            </span>
                            <span className="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible z-50" role="tooltip">
                                <span className="tooltip-body rounded-lg bg-dim px-3 py-2 text-xs normal-case shadow-md">{tooltip}</span>
                            </span>
                        </span>
                    )}
                </h2>
                {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
            </div>

            {(search || filters.length > 0) && (
                <div className="flex flex-col gap-3 border-b border-edge px-5 pb-4 md:flex-row md:items-center">
                    {search && (
                        <div className="relative w-full md:w-64">
                            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-dim-foreground" />
                            <input
                                type="text"
                                value={searchValue}
                                onChange={(e) => handleSearch(e.target.value)}
                                placeholder={searchPlaceholder}
                                className="h-9 w-full rounded-lg border border-field bg-canvas pl-9 pr-3 text-sm outline-none transition-colors placeholder:text-dim-foreground focus:border-brand focus:ring-2 focus:ring-focusring/30"
                            />
                        </div>
                    )}
                    {filters.map((filter) => (
                        <select
                            key={filter.name}
                            name={filter.name}
                            value={filter.value}
                            onChange={(e) => handleFilter(filter.name, e.target.value)}
                            className={cn(
                                'h-9 cursor-pointer rounded-lg border border-field bg-card px-3 text-sm outline-none transition-colors focus:border-brand focus:ring-2 focus:ring-focusring/30',
                                filter.name === 'payroll_period_id' && 'md:w-auto'
                            )}
                        >
                            {filter.options.map((opt) => (
                                <option key={String(opt.value)} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    ))}
                    {hasActiveFilters() && (
                        <Link
                            href={baseUrl.split('?')[0]}
                            className="inline-flex h-8 items-center rounded-lg border border-edge px-3 text-xs font-medium no-underline transition-colors hover:bg-dim"
                            preserveScroll
                        >
                            Clear
                        </Link>
                    )}
                </div>
            )}

            {children}

            {paginator && paginator.total > 0 && (
                <div className="border-t border-edge px-6 py-3">
                    <Pagination paginator={paginator} />
                </div>
            )}

            {empty && (!paginator || paginator.total === 0) && (
                <div className="px-6 py-12 text-center">
                    <span className="icon-[tabler--database-off] mx-auto mb-3 block size-10 text-dim-foreground/50" />
                    <p className="text-sm text-dim-foreground">{typeof empty === 'string' ? empty : 'No records found.'}</p>
                </div>
            )}
        </div>
    );
}

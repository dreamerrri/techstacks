import { useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import Icon from './Icon';
import Pagination from './Pagination';

function debounce(fn, delay) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
}

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
    const [searchValue, setSearchValue] = useState(() => {
        const params = new URLSearchParams(url.split('?')[1] || '');
        return params.get('search') || '';
    });

    const submitSearch = debounce((value) => {
        const params = new URLSearchParams(baseUrl.split('?')[1] || '');
        if (value) params.set('search', value);
        else params.delete('search');
        params.delete('page');
        router.get(baseUrl.split('?')[0], Object.fromEntries(params), {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, 400);

    const handleSearch = (value) => {
        setSearchValue(value);
        submitSearch(value);
    };

    const handleFilter = (name, value) => {
        const params = new URLSearchParams(baseUrl.split('?')[1] || '');
        if (value) params.set(name, value);
        else params.delete(name);
        params.delete('page');
        router.get(baseUrl.split('?')[0], Object.fromEntries(params), {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    const hasActiveFilters = () => {
        const params = new URLSearchParams(url.split('?')[1] || '');
        return [...params.keys()].some((k) => ['search', 'per_page', ...filters.map((f) => f.name)].includes(k));
    };

    return (
        <div className="card w-full min-w-0 border border-base-300 flex flex-col p-0">
            <div className="sticky top-0 px-4 sm:px-7 pt-5 rounded-t-2xl bg-base-100 z-10">
                <div className="flex justify-between items-center mb-4 flex-wrap gap-2">
                    <h2 className="text-sm font-semibold uppercase tracking-widest text-base-content/40 flex items-center gap-2 m-0">
                        {icon && <Icon name={icon} className="size-4 text-primary" />}
                        <span>{title}</span>
                        {tooltip && (
                            <span className="tooltip [--placement:right]">
                                <span className="tooltip-toggle cursor-pointer text-base-content">
                                    <Icon name="tabler--info-circle" className="size-4" />
                                </span>
                                <span className="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
                                    <span className="tooltip-body bg-primary shadow-md rounded-lg px-3 py-2 text-xs normal-case text-primary-content">
                                        {tooltip}
                                    </span>
                                </span>
                            </span>
                        )}
                    </h2>
                    {actions && <div className="flex gap-2">{actions}</div>}
                </div>

                {(search || filters.length > 0) && (
                    <div className="flex flex-col md:flex-row md:items-center gap-3 pb-4">
                        {search && (
                            <div className="join flex-none w-full md:w-64 min-w-40">
                                <input
                                    type="text"
                                    value={searchValue}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    placeholder={searchPlaceholder}
                                    className="input input-bordered input-sm bg-base-200 join-item w-full"
                                />
                                <button type="button" className="btn btn-soft btn-primary btn-sm join-item" onClick={() => handleSearch(searchValue)}>
                                    <Icon name="tabler--search" className="size-4" />
                                </button>
                            </div>
                        )}
                        {filters.map((filter) => (
                            <select
                                key={filter.name}
                                name={filter.name}
                                value={filter.value}
                                onChange={(e) => handleFilter(filter.name, e.target.value)}
                                className="select select-bordered select-sm"
                            >
                                {filter.options.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        ))}
                        {hasActiveFilters() && (
                            <Link href={baseUrl.split('?')[0]} className="btn btn-soft btn-sm" preserveScroll>
                                Clear
                            </Link>
                        )}
                    </div>
                )}
            </div>

            {children}

            {paginator && paginator.total > 0 && (
                <div className="px-6 py-4 border-t border-base-300">
                    <Pagination paginator={paginator} />
                </div>
            )}

            {empty && (!paginator || paginator.total === 0) && (
                <div className="px-6 py-10 text-center">
                    <Icon name="tabler--database-off" className="size-10 text-base-content/30 mx-auto mb-3" />
                    <p className="text-sm text-base-content/60">{typeof empty === 'string' ? empty : 'No records found.'}</p>
                </div>
            )}
        </div>
    );
}
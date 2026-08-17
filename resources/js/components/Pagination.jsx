import { Link } from '@inertiajs/react';
import Icon from './Icon';

function NavButton({ href, disabled, icon, current }) {
    if (disabled) {
        return (
            <button type="button" className="btn btn-disabled" disabled>
                <Icon name={icon} className="size-5 rtl:rotate-180" />
            </button>
        );
    }
    if (current) {
        return (
            <button type="button" className="btn btn-primary" aria-current="page">
                <Icon name={icon} className="size-5 rtl:rotate-180" />
            </button>
        );
    }
    return (
        <Link href={href} className="btn" preserveScroll preserveState>
            <Icon name={icon} className="size-5 rtl:rotate-180" />
        </Link>
    );
}

function PageLink({ href, label, current }) {
    if (current) {
        return (
            <button type="button" className="text-sm btn btn-primary btn-square" aria-current="page">
                {label}
            </button>
        );
    }
    if (!href) {
        return (
            <button type="button" className="text-sm btn btn-square btn-disabled" disabled>
                {label}
            </button>
        );
    }
    return (
        <Link href={href} className="text-sm btn btn-square" preserveScroll preserveState>
            {label}
        </Link>
    );
}

export default function Pagination({ paginator }) {
    if (!paginator || paginator.total === 0) return null;

    const { links, from, to, total } = paginator;
    if (!links || links.length === 0) return null;

    const isPrev = (i) => i === 0;
    const isNext = (i) => i === links.length - 1;

    return (
        <div className="flex items-center justify-between flex-wrap gap-3">
            <p className="text-sm text-base-content mt-2">
                Showing {from} to {to} of {total} results
            </p>
            <nav className="flex items-center justify-end gap-x-1">
                {links.map((link, i) => {
                    if (isPrev(i)) {
                        return (
                            <NavButton
                                key={i}
                                href={link.url}
                                disabled={!link.url}
                                icon="tabler--chevron-left"
                            />
                        );
                    }
                    if (isNext(i)) {
                        return (
                            <NavButton
                                key={i}
                                href={link.url}
                                disabled={!link.url}
                                icon="tabler--chevron-right"
                            />
                        );
                    }

                    const label = link.label.trim();
                    return (
                        <PageLink key={i} href={link.url} label={label} current={link.active} />
                    );
                })}
            </nav>
        </div>
    );
}
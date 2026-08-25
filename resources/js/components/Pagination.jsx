import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

function NavButton({ href, disabled, icon, current, children }) {
    const base = cn(
        'inline-flex h-8 min-w-8 items-center justify-center gap-1 rounded-lg border px-2 text-sm font-medium transition-colors'
    );
    if (disabled) {
        return (
            <button type="button" disabled className={cn(base, 'cursor-not-allowed border-edge/50 text-dim-foreground/50')}>
                {children ?? <ChevronLeft className="size-4 rtl:rotate-180" />}
            </button>
        );
    }
    if (current) {
        return (
            <button type="button" aria-current="page" className={cn(base, 'border-brand bg-brand text-brand-foreground')}>
                {children}
            </button>
        );
    }
    return (
        <Link
            href={href}
            preserveScroll
            preserveState
            className={cn(
                base,
                'border-edge text-canvas-foreground no-underline hover:bg-dim',
                icon && 'px-0'
            )}
        >
            {children ?? <ChevronRight className="size-4 rtl:rotate-180" />}
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
        <div className="flex flex-wrap items-center justify-between gap-3">
            <p className="m-0 text-sm text-dim-foreground">
                Showing <span className="font-semibold text-canvas-foreground">{from}</span>–
                <span className="font-semibold text-canvas-foreground">{to}</span> of{' '}
                <span className="font-semibold text-canvas-foreground">{total}</span> results
            </p>
            <nav className="flex items-center justify-end gap-1" aria-label="Pagination">
                {links.map((link, i) => {
                    if (isPrev(i)) {
                        return (
                            <NavButton key={i} href={link.url} disabled={!link.url} icon>
                                <ChevronLeft className="size-4 rtl:rotate-180" />
                            </NavButton>
                        );
                    }
                    if (isNext(i)) {
                        return (
                            <NavButton key={i} href={link.url} disabled={!link.url} icon>
                                <ChevronRight className="size-4 rtl:rotate-180" />
                            </NavButton>
                        );
                    }

                    const label = String(link.label).trim();
                    return (
                        <NavButton key={i} href={link.url} current={link.active}>
                            {label}
                        </NavButton>
                    );
                })}
            </nav>
        </div>
    );
}

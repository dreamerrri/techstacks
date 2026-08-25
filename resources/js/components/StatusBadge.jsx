import { Badge } from '@/components/ui/badge';

const STYLES = {
    success: 'border-transparent bg-brand/12 text-brand',
    error: 'border-transparent bg-danger/12 text-danger',
    warning: 'border-transparent bg-warning/15 text-warning',
    info: 'border-transparent bg-highlight/12 text-highlight',
    primary: 'border-transparent bg-brand/12 text-brand',
    neutral: 'border-edge bg-dim text-dim-foreground',
};

export default function StatusBadge({ type = 'neutral', icon, children, className = '' }) {
    return (
        <span className={`inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium ${STYLES[type] ?? STYLES.neutral} ${className}`}>
            {children}
        </span>
    );
}

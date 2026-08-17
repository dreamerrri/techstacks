import Icon from './Icon';

const STYLES = {
    success: 'badge-soft badge-success',
    error: 'badge-soft badge-error',
    warning: 'badge-soft badge-warning',
    info: 'badge-soft badge-info',
    primary: 'badge-soft badge-primary',
    neutral: 'badge-soft badge-neutral',
};

const ICONS = {
    success: 'tabler--circle-check',
    error: 'tabler--circle-x',
    warning: 'ph--warning-circle-fill',
    info: 'ph--info-fill',
    primary: 'tabler--shield-check',
};

export default function StatusBadge({ type = 'neutral', icon, children, className = '' }) {
    return (
        <span className={`badge badge-soft ${STYLES[type] ?? STYLES.neutral} ${className}`}>
            {icon && <Icon name={icon} className="size-3.5" />}
            {!icon && ICONS[type] && <Icon name={ICONS[type]} className="size-3.5" />}
            {children}
        </span>
    );
}
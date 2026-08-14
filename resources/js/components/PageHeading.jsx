import Icon from './Icon';

export default function PageHeading({ badge, badgeIcon, badgeColor = 'info', title, subtitle, actions }) {
    return (
        <div className="flex justify-between items-center flex-wrap gap-3 mb-6">
            <div>
                {badge && (
                    <span className={`badge badge-soft badge-${badgeColor} mb-2`}>
                        {badgeIcon && <Icon name={badgeIcon} className="size-4" />}
                        {badge}
                    </span>
                )}
                <h2 className="text-lg font-bold text-base-content mt-2 mb-1">{title}</h2>
                {subtitle && <p className="text-subtle m-0">{subtitle}</p>}
            </div>
            {actions && <div className="flex gap-2 flex-wrap">{actions}</div>}
        </div>
    );
}
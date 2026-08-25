import Icon from './Icon';

export default function Panel({ children, className = '', padding = 'p-6', ...rest }) {
    return (
        <div className={`panel ${padding} ${className}`} {...rest}>
            {children}
        </div>
    );
}

export function PanelHeader({ icon, color = 'text-brand', bg = 'bg-primary/10', title, action }) {
    return (
        <div className="flex items-center justify-between mb-4">
            <h2 className="text-sm font-bold text-base-content flex items-center gap-2">
                <span className={`w-7 h-7 rounded-md ${bg} flex items-center justify-center ${color} text-xs flex-shrink-0`}>
                    {icon && <Icon name={icon} className="size-4" />}
                </span>
                {title}
            </h2>
            {action}
        </div>
    );
}
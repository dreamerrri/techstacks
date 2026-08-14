import { Link } from '@inertiajs/react';
import Icon from './Icon';

export default function Breadcrumbs({ items }) {
    if (!items || items.length === 0) return null;

    return (
        <div className="breadcrumb-trail flex items-center" data-depth={items.length}>
            {items.map((item, index) => {
                const isLast = index === items.length - 1;
                return (
                    <span key={index}>
                        {index > 0 && (
                            <span className="mx-3 inline-flex items-center text-base-content">
                                <Icon name="tabler--chevron-right" className="text-xs" />
                            </span>
                        )}
                        <span className="breadcrumb-item inline-flex items-center" style={{ animationDelay: `${index * 60}ms` }}>
                            {isLast ? (
                                <span className="text-primary font-semibold">{item.label}</span>
                            ) : item.href ? (
                                <Link
                                    href={item.href}
                                    className="text-base-content no-underline transition-colors duration-200 hover:text-primary"
                                >
                                    {item.label}
                                </Link>
                            ) : (
                                <span className="text-base-content">{item.label}</span>
                            )}
                        </span>
                    </span>
                );
            })}
        </div>
    );
}
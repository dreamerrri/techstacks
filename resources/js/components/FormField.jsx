import Icon from './Icon';

export default function FormField({ label, required, error, help, children, className = '', wrapperClass = '' }) {
    return (
        <div className={`fieldset ${wrapperClass}`}>
            {label && (
                <label className="label text-xs font-semibold uppercase tracking-wider text-base-content/60">
                    {label}
                    {required && <span className="text-error"> *</span>}
                </label>
            )}
            {children}
            {error && (
                <p className="label text-error text-xs mt-1 flex items-center gap-1">
                    <Icon name="tabler--circle-x" className="size-3.5" />
                    {error}
                </p>
            )}
            {help && !error && <p className="label text-base-content/50 text-xs mt-1">{help}</p>}
        </div>
    );
}

export function FieldError({ error }) {
    if (!error) return null;
    const message = typeof error === 'string' ? error : (error || []).join(', ');
    return (
        <p className="label text-error text-xs mt-1 flex items-center gap-1">
            <Icon name="tabler--circle-x" className="size-3.5" />
            {message}
        </p>
    );
}
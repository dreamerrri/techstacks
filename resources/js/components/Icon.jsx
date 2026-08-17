export default function Icon({ name, className = 'size-5' }) {
    if (!name) return null;
    return <i aria-hidden="true" className={`icon-[${name}] ${className}`} />;
}
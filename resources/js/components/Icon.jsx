export default function Icon({ name, className = 'size-5' }) {
    if (!name) return null;
    // Normalize: accept either a bare name ('tabler--users') or a full
    // 'icon-[tabler--users]' class string, never double-wrapping it.
    const normalized = name.startsWith('icon-[') ? name.slice(6, -1) : name;
    return <i aria-hidden="true" className={`icon-[${normalized}] ${className}`} />;
}

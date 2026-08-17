export default function Avatar({ src, name, size = 'size-9.5', className = '', rounded = 'rounded-full' }) {
    const initial = name ? name.charAt(0).toUpperCase() : '?';
    return (
        <div className={`avatar ${className}`}>
            <div className={`${size} ${rounded}`}>
                {src ? (
                    <img src={src} alt={name || 'avatar'} />
                ) : (
                    <span className={`flex items-center justify-center bg-base-200 size-full ${rounded}`}>{initial}</span>
                )}
            </div>
        </div>
    );
}
export default function DetailRow({ label, children, border = true }) {
    return (
        <div className={`flex justify-between items-center py-3 gap-4 ${border ? 'border-b border-edge' : ''}`}>
            <span className="text-dim-foreground/70 font-medium">{label}</span>
            <span className="font-medium text-base-content text-right">{children}</span>
        </div>
    );
}
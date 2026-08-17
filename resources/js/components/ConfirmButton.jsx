import { useRef } from 'react';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/react';

const themeColors = () => {
    if (typeof document === 'undefined') return { error: '#dc2626', neutral: '#6b7280' };
    const style = getComputedStyle(document.documentElement);
    return {
        error: style.getPropertyValue('--color-error').trim() || '#dc2626',
        neutral: style.getPropertyValue('--color-neutral').trim() || '#6b7280',
    };
};

export default function ConfirmButton({
    title = 'Confirm Action',
    text = 'Are you sure you want to proceed?',
    icon = 'warning',
    confirmText = 'Yes, proceed',
    cancelText = 'Cancel',
    confirmButtonColor,
    cancelButtonColor,
    onConfirm,
    method = 'delete',
    url,
    className = 'btn btn-soft btn-error btn-sm',
    children,
    disabled = false,
}) {
    const confirmRef = useRef(onConfirm);

    const handleClick = () => {
        const colors = themeColors();
        Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor || colors.error,
            cancelButtonColor: cancelButtonColor || colors.neutral,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
        }).then((result) => {
            if (!result.isConfirmed) return;
            if (url) {
                router[method](url, {}, { preserveScroll: true });
            } else if (confirmRef.current) {
                confirmRef.current();
            }
        });
    };

    return (
        <button type="button" className={className} onClick={handleClick} disabled={disabled}>
            {children}
        </button>
    );
}
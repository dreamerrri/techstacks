import { useRef } from 'react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { router } from '@inertiajs/react';

const themeColors = () => {
    if (typeof document === 'undefined') return { error: '#dc2626', neutral: '#6b7280' };
    const style = getComputedStyle(document.documentElement);
    return {
        error: style.getPropertyValue('--sc-destructive').trim() || '#dc2626',
        neutral: style.getPropertyValue('--color-dim').trim() || '#6b7280',
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
    className = '',
    children,
    disabled = false,
}) {
    const confirmRef = useRef(onConfirm);
    confirmRef.current = onConfirm;

    const colors = themeColors();

    const handleConfirmed = () => {
        if (url) {
            router[method](url, {}, { preserveScroll: true });
        } else if (confirmRef.current) {
            confirmRef.current();
        }
    };

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <button type="button" className={className} disabled={disabled}>
                    {children}
                </button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>{text}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel className="cursor-pointer">{cancelText}</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={handleConfirmed}
                        className="cursor-pointer"
                        style={{ backgroundColor: confirmButtonColor || colors.error, color: '#fff' }}
                    >
                        {confirmText}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

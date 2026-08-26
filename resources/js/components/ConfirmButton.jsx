import { useRef } from 'react';
import * as AlertDialog from '@radix-ui/react-alert-dialog';
import { router } from '@inertiajs/react';

/**
 * Confirmation dialog built on Radix AlertDialog, styled with FlyonUI
 * theme variables so it matches whichever data-theme is active.
 * Same props API as before (title/text/confirmText/url/method/onConfirm).
 */
export default function ConfirmButton({
    title = 'Confirm Action',
    text = 'Are you sure you want to proceed?',
    confirmText = 'Yes, proceed',
    cancelText = 'Cancel',
    onConfirm,
    method = 'delete',
    url,
    className = '',
    children,
    disabled = false,
}) {
    const confirmRef = useRef(onConfirm);
    confirmRef.current = onConfirm;

    const handleConfirmed = () => {
        if (url) {
            router[method](url, {}, { preserveScroll: true });
        } else if (confirmRef.current) {
            confirmRef.current();
        }
    };

    return (
        <AlertDialog.Root>
            <AlertDialog.Trigger asChild>
                <button type="button" className={className} disabled={disabled}>
                    {children}
                </button>
            </AlertDialog.Trigger>
            <AlertDialog.Portal>
                <AlertDialog.Overlay className="confirm-overlay" />
                <AlertDialog.Content className="confirm-panel">
                    <AlertDialog.Title className="confirm-title">{title}</AlertDialog.Title>
                    <AlertDialog.Description className="confirm-description">
                        {text}
                    </AlertDialog.Description>
                    <div className="confirm-footer">
                        <AlertDialog.Cancel className="confirm-cancel">
                            {cancelText}
                        </AlertDialog.Cancel>
                        <AlertDialog.Action
                            className="confirm-action"
                            onClick={handleConfirmed}
                        >
                            {confirmText}
                        </AlertDialog.Action>
                    </div>
                </AlertDialog.Content>
            </AlertDialog.Portal>
        </AlertDialog.Root>
    );
}

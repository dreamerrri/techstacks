export function initToast() {
    if (typeof Swal === 'undefined') return;

    window.Toast = Swal.mixin({
        toast:             true,
        position:          'top-end',
        showConfirmButton: false,
        timer:             3500,
        timerProgressBar:  true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });
}

export function initConfirmDialogs() {
    if (typeof Swal === 'undefined') return;

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = form.dataset.confirm      || 'Are you sure you want to proceed?';
            const title   = form.dataset.confirmTitle || 'Confirm Action';
            const icon    = form.dataset.confirmIcon  || 'warning';
            const btnText = form.dataset.confirmBtn   || 'Yes, proceed';

            Swal.fire({
                title, text: message, icon,
                showCancelButton:   true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor:  '#6b7280',
                confirmButtonText:  btnText,
                cancelButtonText:   'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
}
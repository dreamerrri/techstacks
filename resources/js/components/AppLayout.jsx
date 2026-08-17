import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import Sidebar from './Sidebar';
import Navbar from './Navbar';
import { toast } from './toast';
import getBreadcrumbs from '../Config/breadcrumbs';

export default function AppLayout({ children, title }) {
    const { props, url } = usePage();
    const [mobileOpen, setMobileOpen] = useState(false);
    const [minified, setMinified] = useState(() => sessionStorage.getItem('sidebar_collapsed') === '1');

    const flash = props.flash ?? {};
    const role = props.role ?? 'user';
    const breadcrumbs = getBreadcrumbs(url, role, title);

    useEffect(() => {
        document.body.dataset.role = role;
    }, [role]);

    useEffect(() => {
        if (title) {
            document.title = `${title} - Techstacks Logify`;
        }
    }, [title]);

    useEffect(() => {
        const types = ['success', 'error', 'warning', 'info'];
        types.forEach((type) => {
            const msg = flash[type];
            if (msg) {
                toast(type, msg);
            }
        });
    }, [flash.success, flash.error, flash.warning, flash.info]);

    const toggleMinified = () => {
        setMinified((v) => {
            const next = !v;
            if (next) {
                sessionStorage.setItem('sidebar_collapsed', '1');
                document.body.classList.add('overlay-minified');
            } else {
                sessionStorage.removeItem('sidebar_collapsed');
                document.body.classList.remove('overlay-minified');
            }
            return next;
        });
    };

    return (
        <div className="flex flex-col flex-1 min-w-0 sm:ps-[var(--sidebar-w)] overlay-minified:sm:ps-[var(--sidebar-w-mini)] transition-[padding] duration-300">
            <Sidebar
                open={mobileOpen}
                onClose={() => setMobileOpen(false)}
                minified={minified}
                onToggleMinified={toggleMinified}
            />

            <div className="flex flex-col flex-1 min-w-0">
                <Navbar onOpenSidebar={() => setMobileOpen(true)} onToggleMinified={toggleMinified} breadcrumbs={breadcrumbs} />

                <main className="min-w-0 p-3 sm:p-4 overflow-x-hidden">
                    {children}
                </main>
            </div>
        </div>
    );
}
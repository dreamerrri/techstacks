import { useEffect, useRef, useState } from 'react';
import { Bell, LogOut, Menu, PanelLeftClose, PanelLeftOpen, Search, User as UserIcon } from 'lucide-react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import ThemeDropdown from './ThemeDropdown';
import Breadcrumbs from './Breadcrumbs';
import SearchModal from './SearchModal';

const NOTIF_STYLE = {
    alert: 'text-highlight',
    error: 'text-danger',
    warning: 'text-warning',
    success: 'text-success',
    info: 'text-brand',
    default: 'text-dim-foreground',
};

export default function Navbar({ onOpenSidebar, onToggleMinified, breadcrumbs = [], minified = false }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const notifications = props.notifications ?? [];
    const notifCount = props.notifCount ?? 0;
    const [searchOpen, setSearchOpen] = useState(false);
    
    useEffect(() => {
        const onKey = (e) => {
            if (e.key === 'Escape') setSearchOpen(false);
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, []);

    const markAsRead = (e, notification) => {
        e.preventDefault();
        router.post(`/notifications/${notification.id}/mark-read`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.link) window.location.href = notification.link;
            },
        });
    };

    const markAllRead = () => router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
    const logout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    const roleLabel = user?.role === 'admin' ? 'Administrator' : user?.role === 'hr' ? 'HR Personnel' : 'Employee';

    return (
        <>
            <nav className="sticky top-0 z-20 flex h-14 items-center gap-2 border-b border-edge bg-canvas px-3 sm:gap-4 sm:px-4">
                <div className="flex min-w-0 flex-1 items-center gap-2">
                    <Button variant="ghost" size="icon" className="sm:hidden" onClick={onOpenSidebar} aria-label="Open menu">
                        <Menu className="size-5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="hidden sm:inline-flex"
                        onClick={onToggleMinified}
                        aria-label="Toggle sidebar"
                    >
                        {minified ? <PanelLeftOpen className="size-5" /> : <PanelLeftClose className="size-5" />}
                    </Button>
                    <Breadcrumbs items={breadcrumbs} />
                </div>

                <div className="flex shrink-0 items-center gap-1.5 sm:gap-2.5">
                    <button
                        type="button"
                        onClick={() => setSearchOpen(true)}
                        aria-haspopup="dialog"
                        aria-expanded={searchOpen}
                        className="flex h-9 w-full max-w-xs cursor-pointer items-center gap-2 rounded-lg border border-edge bg-card px-3 text-start text-sm text-dim-foreground transition-colors hover:bg-dim md:w-56"
                    >
                        <Search className="size-4 shrink-0" />
                        <span className="flex-1 truncate">Search…</span>
                    </button>

                    <ThemeDropdown />

                    {/* Notifications */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon" className="relative" aria-label="Notifications">
                                <Bell className="size-5" />
                                {notifCount > 0 && (
                                    <span className="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-danger text-[10px] font-bold text-danger-foreground">
                                        {notifCount > 9 ? '9+' : notifCount}
                                    </span>
                                )}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-80">
                            <DropdownMenuLabel className="flex items-center justify-between">
                                Notifications
                                {notifCount > 0 && (
                                    <button type="button" onClick={markAllRead} className="cursor-pointer text-xs font-normal text-brand hover:underline">
                                        Mark all read
                                    </button>
                                )}
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {notifications.length > 0 ? (
                                <div className="max-h-[380px] overflow-y-auto">
                                    {notifications.map((notification) => (
                                        <a
                                            key={notification.id}
                                            href={notification.link || '#'}
                                            onClick={(e) => markAsRead(e, notification)}
                                            className="flex items-start gap-3 border-b border-edge px-3 py-2.5 no-underline outline-none last:border-b-0 hover:bg-dim"
                                        >
                                            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-dim">
                                                <Bell className={`size-4 ${NOTIF_STYLE[notification.type] ?? NOTIF_STYLE.default}`} />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className="truncate text-[13px] font-semibold">{notification.title}</div>
                                                <div className="mt-px truncate text-xs text-dim-foreground">{notification.message}</div>
                                                {notification.created_at && (
                                                    <div className="mt-0.5 text-[10px] text-dim-foreground/70">{notification.created_at}</div>
                                                )}
                                            </div>
                                        </a>
                                    ))}
                                </div>
                            ) : (
                                <div className="px-3 py-8 text-center">
                                    <p className="m-0 text-[13px] font-semibold">All caught up</p>
                                    <p className="m-0 mt-1 text-xs text-dim-foreground">No pending actions right now</p>
                                </div>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>

                    {/* Avatar menu */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <button type="button" className="flex rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focusring" aria-label="Account menu">
                                <div className="avatar size-9 overflow-hidden rounded-full">
                                    <div className="size-full">
                                        {user?.photo_url ? (
                                            <img src={user.photo_url} alt={user.name} />
                                        ) : (
                                            <span className="flex size-full items-center justify-center rounded-full bg-soft text-sm font-bold text-soft-foreground">
                                                {user?.name ? user.name.charAt(0).toUpperCase() : '?'}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-56">
                            <DropdownMenuLabel>
                                <div className="text-sm font-semibold">{user?.name}</div>
                                <div className="text-xs font-normal text-dim-foreground">{roleLabel}</div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link href="/profile" className="flex w-full cursor-pointer items-center gap-2">
                                    <UserIcon className="size-4" /> My Profile
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                onClick={logout}
                                className="cursor-pointer text-danger focus:text-danger"
                            >
                                <LogOut className="size-4" /> Sign out
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </nav>

            <SearchModal open={searchOpen} onClose={() => setSearchOpen(false)} />
        </>
    );
}

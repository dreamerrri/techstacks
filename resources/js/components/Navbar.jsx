import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import Icon from './Icon';
import Breadcrumbs from './Breadcrumbs';
import SearchModal from './SearchModal';

const NOTIF_STYLE = {
    alert: { bg: 'bg-error/10', text: 'text-error', icon: 'ph--warning-fill' },
    error: { bg: 'bg-error/10', text: 'text-error', icon: 'tabler--circle-x' },
    warning: { bg: 'bg-warning/10', text: 'text-warning', icon: 'ph--warning-circle-fill' },
    success: { bg: 'bg-success/10', text: 'text-success', icon: 'tabler--circle-check' },
    info: { bg: 'bg-info/10', text: 'text-info', icon: 'ph--info-fill' },
    default: { bg: 'bg-base-300', text: 'text-subtle', icon: 'ph--bell-fill' },
};

export default function Navbar({ onOpenSidebar, onToggleMinified, breadcrumbs = [] }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const notifications = props.notifications ?? [];
    const notifCount = props.notifCount ?? 0;
    const [notifOpen, setNotifOpen] = useState(false);
    const [avatarOpen, setAvatarOpen] = useState(false);
    const [searchOpen, setSearchOpen] = useState(false);
    const notifRef = useRef(null);
    const avatarRef = useRef(null);

    useEffect(() => {
        const onDocClick = (e) => {
            if (notifRef.current && !notifRef.current.contains(e.target)) setNotifOpen(false);
            if (avatarRef.current && !avatarRef.current.contains(e.target)) setAvatarOpen(false);
        };
        const onKey = (e) => {
            if (e.key === 'Escape') {
                setNotifOpen(false);
                setAvatarOpen(false);
            }
            if ((e.metaKey || e.ctrlKey) && e.key === '/') {
                e.preventDefault();
                setSearchOpen(true);
            }
        };
        document.addEventListener('mousedown', onDocClick);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('mousedown', onDocClick);
            document.removeEventListener('keydown', onKey);
        };
    }, []);

    const isAdmin = user?.role === 'admin';
    const isHR = user?.role === 'hr';

    const markAsRead = (e, notification) => {
        e.preventDefault();
        router.post(`/notifications/${notification.id}/mark-read`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.link) {
                    window.location.href = notification.link;
                }
            },
        });
    };

    const markAllRead = () => {
        router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
    };

    const logout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    const roleLabel = isAdmin ? 'Administrator' : isHR ? 'HR Personnel' : 'Employee';

    return (
        <>
            <nav className="navbar bg-base-100 gap-2 sm:gap-4 border-b border-base-300 shadow-sm sticky top-0 z-9">
                <div className="navbar-start min-w-0 items-center gap-2">
                    <button type="button" className="btn btn-text max-sm:btn-square sm:hidden" onClick={onOpenSidebar} aria-label="Open menu">
                        <Icon name="tabler--menu-2" className="size-5" />
                    </button>

                    <div className="hidden sm:block">
                        <button type="button" className="btn btn-circle btn-text" onClick={onToggleMinified} aria-label="Minify navigation">
                            <Icon name="tabler--menu-2" className="size-5" />
                        </button>
                    </div>

                    <div className="hidden md:flex min-w-0 items-center ps-2">
                        <Breadcrumbs items={breadcrumbs} />
                    </div>
                </div>

                <div className="navbar-end min-w-0 flex items-center gap-2 sm:gap-4">
                    {/* Search */}
                    <button
                        type="button"
                        className="input input-bordered btn-outline bg-base-200 flex w-full max-w-xs input-sm items-center gap-2 text-start text-subtle cursor-pointer"
                        onClick={() => setSearchOpen(true)}
                        aria-haspopup="dialog"
                        aria-expanded={searchOpen}
                    >
                        <Icon name="tabler--search" className="size-4 shrink-0" />
                        <span className="truncate flex-1">Search or type a command</span>
                        <kbd className="kbd kbd-sm hidden sm:inline-flex">Ctrl /</kbd>
                    </button>

                    {/* Notifications */}
                    <div ref={notifRef} className={`dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end] --prevent-on-load-init ${notifOpen ? 'open' : ''}`}>
                        <button
                            type="button"
                            className="dropdown-toggle btn btn-text btn-circle relative"
                            aria-haspopup="menu"
                            aria-expanded={notifOpen}
                            aria-label="Notifications"
                            onClick={() => setNotifOpen((v) => !v)}
                        >
                            <Icon name="tabler--bell" className="size-5" />
                            {notifCount > 0 && (
                                <span className="badge badge-error badge-sm absolute -top-1 -end-1">
                                    {notifCount > 9 ? '9+' : notifCount}
                                </span>
                            )}
                        </button>
                        {notifOpen && (
                            <div className="dropdown-menu dropdown-open:opacity-100 absolute end-0 top-full mt-2 min-w-72 z-50" role="menu" aria-labelledby="notif-dropdown">
                                <div className="dropdown-header justify-between">
                                    <span>Notifications</span>
                                    {notifCount > 0 && (
                                        <button type="button" className="btn btn-text btn-xs" onClick={markAllRead}>
                                            Mark all read
                                        </button>
                                    )}
                                </div>
                                <div className="max-h-[380px] overflow-y-auto">
                                    {notifications.length > 0 ? (
                                        <ul role="list" className="list-none divide-y divide-base-300">
                                            {notifications.map((notification) => {
                                                const style = NOTIF_STYLE[notification.type] ?? NOTIF_STYLE.default;
                                                return (
                                                    <li key={notification.id} role="listitem">
                                                        <a
                                                            href={notification.link || '#'}
                                                            onClick={(e) => markAsRead(e, notification)}
                                                            className="group flex items-center gap-3 bg-base-100 px-4 py-[11px] no-underline outline-none transition-colors duration-150 hover:bg-base-200 focus-visible:bg-base-200 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary/30"
                                                        >
                                                            <div className={`flex h-[34px] w-[34px] flex-shrink-0 items-center justify-center rounded-[9px] ${style.bg}`}>
                                                                <Icon name={style.icon} className={`${style.text} text-sm`} />
                                                            </div>
                                                            <div className="min-w-0 flex-1">
                                                                <div className="truncate text-[13px] font-semibold text-base-content">{notification.title}</div>
                                                                <div className="mt-px text-[11px] text-subtle">{notification.message}</div>
                                                                {notification.created_at && (
                                                                    <div className="mt-0.5 text-[10px] text-faint">{notification.created_at}</div>
                                                                )}
                                                            </div>
                                                            <Icon name="ph--caret-right-fill" className="flex-shrink-0 text-[10px] text-faint group-hover:text-subtle" />
                                                        </a>
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    ) : (
                                        <div className="px-4 py-8 text-center">
                                            <div className="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-success/10">
                                                <Icon name="tabler--check" className="text-lg text-success" />
                                            </div>
                                            <div className="mb-1 text-[13px] font-semibold text-base-content">All caught up</div>
                                            <div className="text-xs text-faint">No pending actions right now</div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Avatar dropdown */}
                    <div ref={avatarRef} className={`dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end] --prevent-on-load-init ${avatarOpen ? 'open' : ''}`}>
                        <button
                            type="button"
                            className="dropdown-toggle flex items-center"
                            aria-haspopup="menu"
                            aria-expanded={avatarOpen}
                            onClick={() => setAvatarOpen((v) => !v)}
                        >
                            <div className="avatar">
                                <div className="size-9.5 rounded-full">
                                    {user?.profile_photo ? (
                                        <img src={user.profile_photo} alt={user.name} />
                                    ) : (
                                        <span className="flex items-center justify-center bg-base-200 size-full rounded-full">
                                            {user?.name ? user.name.charAt(0).toUpperCase() : '?'}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </button>
                        {avatarOpen && (
                            <ul className="dropdown-menu dropdown-open:opacity-100 absolute end-0 top-full mt-2 min-w-60 border border-base-300 z-50" role="menu" aria-labelledby="dropdown-avatar">
                                <li className="dropdown-header gap-2">
                                    <div>
                                        <h6 className="text-base-content text-base font-semibold">{user?.name}</h6>
                                        <small className="text-subtle">{roleLabel}</small>
                                    </div>
                                </li>
                                <li>
                                    <Link className="dropdown-item" href="/profile" onClick={() => setAvatarOpen(false)}>
                                        <Icon name="tabler--user" />
                                        My Profile
                                    </Link>
                                </li>
                                <li className="dropdown-footer gap-2">
                                    <button type="button" className="btn btn-error btn-block" onClick={logout}>
                                        <Icon name="tabler--logout" />
                                        Sign out
                                    </button>
                                </li>
                            </ul>
                        )}
                    </div>
                </div>
            </nav>

            <SearchModal open={searchOpen} onClose={() => setSearchOpen(false)} />
        </>
    );
}
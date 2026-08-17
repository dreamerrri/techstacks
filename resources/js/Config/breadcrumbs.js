import NAV from './nav';

const LEAF_SEGMENTS = {
    create: 'Create',
    edit: 'Edit',
    archived: 'Archived',
    pending: 'Pending',
};

function humanize(segment) {
    if (/^\d+$/.test(segment)) return 'Details';
    return segment
        .split('-')
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function findSection(roleNav, firstSegment) {
    return (roleNav.flat ?? []).find((item) => (item.active ?? []).includes(firstSegment));
}

export default function getBreadcrumbs(url, role, pageTitle) {
    const path = (url || '/').split('?')[0];
    const segments = path.split('/').filter(Boolean);
    const roleNav = NAV[role] ?? NAV.user;

    const items = [{ label: 'Dashboard', href: '/dashboard' }];

    if (segments.length === 0 || segments[0] === 'dashboard') {
        return items;
    }

    const first = segments[0];
    const section = findSection(roleNav, first);

    if (section && segments.length > 1) {
        items.push({ label: section.title, href: section.href });
    }

    if (segments.length > 1) {
        const last = segments[segments.length - 1];
        const label = pageTitle || LEAF_SEGMENTS[last] || humanize(last);
        items.push({ label });
    } else if (section) {
        items.push({ label: section.title });
    } else {
        items.push({ label: humanize(first) });
    }

    return items;
}
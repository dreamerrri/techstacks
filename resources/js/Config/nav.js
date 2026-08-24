const NAV = {
    admin: {
        groups: [
            {
                label: 'Access Control',
                icon: 'tabler--lock',
                items: [
                    { title: 'Users', icon: 'tabler--users', href: '/users', active: ['users'] },
                    { title: 'Pending Accounts', icon: 'tabler--user-question', href: '/users/pending', active: ['users'] },
                    { title: 'Roles', icon: 'tabler--shield', href: '/roles', active: ['roles'] },
                    { title: 'Permissions', icon: 'tabler--shield-check', href: '/permissions', active: ['permissions'] },
                ],
            },
            {
                label: 'Workforce',
                icon: 'tabler--users-group',
                items: [
                    { title: 'Employees', icon: 'tabler--id', href: '/employees', active: ['employees'] },
                    { title: 'Attendance', icon: 'tabler--calendar-check', href: '/manual-payroll-attendance', active: ['manual-payroll-attendance', 'payroll-periods'] },
                    { title: 'Work Requests', icon: 'tabler--notes', href: '/work-requests', active: ['work-requests'] },
                    { title: 'Financial Requests', icon: 'tabler--cash', href: '/financial-requests', active: ['financial-requests'] },
                ],
            },
            {
                label: 'Finance',
                icon: 'tabler--wallet',
                items: [
                    { title: 'Payroll', icon: 'tabler--cash', href: '/payroll', active: ['payroll'] },
                    { title: 'Contributions', icon: 'tabler--id-badge', href: '/government-contributions', active: ['government-contributions'] },
                ],
            },
            {
                label: 'Monitoring',
                icon: 'tabler--chart-line',
                items: [
                    { title: 'Audit Logs', icon: 'tabler--file-text', href: '/audit-logs', active: ['audit-logs'] },
                ],
            },
        ],
        flat: [
            { title: 'Dashboard', icon: 'tabler--home', href: '/dashboard', active: ['dashboard'] },
            { title: 'Users', icon: 'tabler--users', href: '/users', active: ['users'] },
            { title: 'Roles', icon: 'tabler--shield', href: '/roles', active: ['roles'] },
            { title: 'Permissions', icon: 'tabler--shield-check', href: '/permissions', active: ['permissions'] },
            { title: 'Employees', icon: 'tabler--id', href: '/employees', active: ['employees'] },
            { title: 'Attendance', icon: 'tabler--calendar-check', href: '/manual-payroll-attendance', active: ['manual-payroll-attendance', 'payroll-periods'] },
            { title: 'Work Requests', icon: 'tabler--notes', href: '/work-requests', active: ['work-requests'] },
            { title: 'Financial Requests', icon: 'tabler--cash', href: '/financial-requests', active: ['financial-requests'] },
            { title: 'Payroll', icon: 'tabler--cash', href: '/payroll', active: ['payroll'] },
            { title: 'Gov. Contributions', icon: 'tabler--id-badge', href: '/government-contributions', active: ['government-contributions'] },
            { title: 'Audit Logs', icon: 'tabler--file-text', href: '/audit-logs', active: ['audit-logs'] },
        ],
    },
    hr: {
        groups: [
            {
                label: 'Workforce',
                icon: 'tabler--users-group',
                items: [
                    { title: 'Employees', icon: 'tabler--id', href: '/employees', active: ['employees'] },
                    { title: 'Attendance', icon: 'tabler--calendar-check', href: '/manual-payroll-attendance', active: ['manual-payroll-attendance', 'payroll-periods'] },
                    { title: 'Work Requests', icon: 'tabler--notes', href: '/work-requests', active: ['work-requests'] },
                    { title: 'Financial Requests', icon: 'tabler--cash', href: '/financial-requests', active: ['financial-requests'] },
                ],
            },
            {
                label: 'Finance',
                icon: 'tabler--wallet',
                items: [
                    { title: 'Payroll', icon: 'tabler--cash', href: '/payroll', active: ['payroll'] },
                    { title: 'Contributions', icon: 'tabler--id-badge', href: '/government-contributions', active: ['government-contributions'] },
                ],
            },
        ],
        flat: [
            { title: 'Dashboard', icon: 'tabler--home', href: '/dashboard', active: ['dashboard'] },
            { title: 'Employees', icon: 'tabler--id', href: '/employees', active: ['employees'] },
            { title: 'Attendance', icon: 'tabler--calendar-check', href: '/manual-payroll-attendance', active: ['manual-payroll-attendance', 'payroll-periods'] },
            { title: 'Work Requests', icon: 'tabler--notes', href: '/work-requests', active: ['work-requests'] },
            { title: 'Financial Requests', icon: 'tabler--cash', href: '/financial-requests', active: ['financial-requests'] },
            { title: 'Payroll', icon: 'tabler--cash', href: '/payroll', active: ['payroll'] },
            { title: 'Gov. Contributions', icon: 'tabler--id-badge', href: '/government-contributions', active: ['government-contributions'] },
        ],
    },
    user: {
        groups: [],
        flat: [
            { title: 'My Profile', icon: 'tabler--user', href: '/profile', active: ['profile'] },
            { title: 'My Payslip', icon: 'tabler--receipt', href: '/payroll', active: ['payroll'] },
            { title: 'Attendance', icon: 'tabler--clock', href: '/employee-attendance', active: ['employee-attendance'] },
            { title: 'Work Requests', icon: 'tabler--notes', href: '/work-requests', active: ['work-requests'] },
            { title: 'Financial Requests', icon: 'tabler--cash', href: '/financial-requests', active: ['financial-requests'] },
        ],
    },
};

export default NAV;
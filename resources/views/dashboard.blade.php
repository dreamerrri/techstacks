<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($user->role === 'admin') Admin
        @elseif($user->role === 'hr') HR
        @else User
        @endif
        Dashboard - HR Management System
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; }

        .sidebar-admin { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
        .sidebar-hr    { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
        .sidebar-user  { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .sidebar { color: white; min-height: 100vh; padding: 20px 0; }

        .sidebar-header { padding: 20px; text-align: center; border-bottom: 2px solid rgba(255,255,255,0.2); margin-bottom: 20px; }
        .sidebar-header h1 { font-size: 24px; margin-bottom: 5px; }
        .sidebar-header p  { font-size: 12px; opacity: 0.8; }

        .nav-item {
            padding: 15px 20px; cursor: pointer; transition: all 0.3s;
            display: flex; align-items: center; gap: 10px;
            margin: 5px 10px; border-radius: 5px;
            text-decoration: none; color: white;
        }
        .nav-item:hover  { background: rgba(255,255,255,0.2); transform: translateX(5px); }
        .nav-item.active { background: rgba(255,255,255,0.3); border-left: 4px solid #fbbf24; }
        .nav-item i      { width: 20px; text-align: center; }

        .logout-btn {
            display: block; width: calc(100% - 40px); margin: 0 20px; padding: 10px;
            background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.5);
            color: white; border-radius: 5px; cursor: pointer; text-align: center; transition: all 0.3s;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }

        .topbar {
            background: white; padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }

        .user-info   { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .avatar-admin { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
        .avatar-hr    { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
        .avatar-user  { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .user-details h3 { margin: 0; font-size: 14px; font-weight: 600; color: #1f2937; }
        .user-details p  { margin: 0; font-size: 12px; color: #6b7280; }

        .role-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-admin { background: #fecaca; color: #991b1b; }
        .badge-hr    { background: #bfdbfe; color: #1e40af; }
        .badge-user  { background: #d1fae5; color: #065f46; }

        .content { padding: 40px; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { color: #1f2937; margin-bottom: 20px; font-size: 22px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card  { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .stat-icon  { font-size: 32px; margin-bottom: 10px; }
        .stat-value { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; }
        .stat-label { color: #6b7280; font-size: 14px; }

        .welcome-message { font-size: 18px; color: #6b7280; margin-bottom: 20px; }
        .role-access-badge { display: inline-block; padding: 8px 15px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 20px; }

        .action-btn { padding: 15px; background: white; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s; width: 100%; }
        .action-btn:hover { opacity: 0.85; }
    </style>
</head>
<body>

@php
    $isAdmin  = $user->role === 'admin';
    $isHR     = $user->role === 'hr';
    $roleKey  = $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user');
    $color    = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
@endphp

<div style="display: grid; grid-template-columns: 250px 1fr; min-height: 100vh;">

    {{-- SIDEBAR --}}
    <div class="sidebar sidebar-{{ $roleKey }}" style="position: relative;">
        <div class="sidebar-header">
            <h1>HR System</h1>
            <p>
                @if($isAdmin) Admin Portal
                @elseif($isHR) HR Portal
                @else Employee Portal
                @endif
            </p>
        </div>

        <nav>
            <a href="{{ route('dashboard') }}" class="nav-item active">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>

            @if($isAdmin)
                <a href="#" class="nav-item"><i class="fas fa-users"></i><span>All Users</span></a>
                <a href="{{ route('employees.index') }}" class="nav-item"><i class="fas fa-user-tie"></i><span>Manage Staff</span></a>
                <a href="#" class="nav-item"><i class="fas fa-lock"></i><span>Access Control</span></a>
                <a href="#" class="nav-item"><i class="fas fa-shield-alt"></i><span>System Security</span></a>
                <a href="#" class="nav-item"><i class="fas fa-cogs"></i><span>Settings</span></a>
                <a href="#" class="nav-item"><i class="fas fa-file-alt"></i><span>Audit Logs</span></a>

            @elseif($isHR)
                <a href="{{ route('employees.index') }}" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
                <a href="#" class="nav-item"><i class="fas fa-money-bill"></i><span>Payroll</span></a>
                <a href="#" class="nav-item"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
                <a href="#" class="nav-item"><i class="fas fa-suitcase"></i><span>Leave Requests</span></a>
                <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>

            @else
                <a href="#" class="nav-item"><i class="fas fa-user"></i><span>My Profile</span></a>
                <a href="#" class="nav-item"><i class="fas fa-file-invoice-dollar"></i><span>Payslips</span></a>
                <a href="#" class="nav-item"><i class="fas fa-calendar-times"></i><span>Leave Request</span></a>
                <a href="#" class="nav-item"><i class="fas fa-clock"></i><span>Attendance</span></a>
            @endif
        </nav>

        <div style="position: absolute; bottom: 20px; width: 100%;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div>
        {{-- Topbar --}}
        <div class="topbar">
            <h2 style="margin: 0; color: #1f2937;">
                @if($isAdmin) Admin Dashboard
                @elseif($isHR) HR Dashboard
                @else Dashboard
                @endif
            </h2>
            <div class="user-info">
                <div class="user-avatar avatar-{{ $roleKey }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="user-details">
                    <h3>{{ $user->name }}</h3>
                    <p>
                        @if($isAdmin) Administrator
                        @elseif($isHR) HR Personnel
                        @else Employee
                        @endif
                    </p>
                </div>
                <span class="role-badge badge-{{ $roleKey }}">{{ ucfirst($user->role) }}</span>
            </div>
        </div>

        {{-- Content --}}
        <div class="content">

            @if($isAdmin)
                <span class="role-access-badge" style="background:#fecaca; color:#991b1b;">
                    <i class="fas fa-shield-alt"></i> Administrator Access
                </span>
            @elseif($isHR)
                <span class="role-access-badge" style="background:#bfdbfe; color:#1e40af;">
                    <i class="fas fa-user-tie"></i> HR Department Access
                </span>
            @endif

            <div class="welcome-message">
                Welcome, {{ $user->name }}!
                @if($isAdmin) You have full administrative access.
                @elseif($isHR) You have HR access privileges.
                @endif
            </div>

            {{-- Stats — driven entirely by $stats from the controller, no hardcoded values --}}
            <div class="stats-grid">
                @foreach($stats as $stat)
                    <div class="stat-card">
                        <div class="stat-icon" style="color: {{ $stat['color'] }};">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </div>
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Actions — also driven by $actions from the controller --}}
            <div class="card">
                <h2>
                    @if($isAdmin) Administrative Actions
                    @elseif($isHR) HR Actions
                    @else Quick Actions
                    @endif
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    @foreach($actions as $action)
                        <button class="action-btn" style="border: 2px solid {{ $color }}; color: {{ $color }};">
                            <i class="fas {{ $action['icon'] }}"></i> {{ $action['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- System Information --}}
            <div class="card">
                <h2>System Information</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 10px 0; color: #6b7280; width: 200px;">Name</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ $user->name }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 10px 0; color: #6b7280;">Email</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ $user->email }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 10px 0; color: #6b7280;">Role</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                            @if($isAdmin) Administrator
                            @elseif($isHR) HR Personnel
                            @else Employee
                            @endif
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 10px 0; color: #6b7280;">Account Status</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                            @if($user->is_active)
                                <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Active</span>
                            @else
                                <span style="color: #dc2626;"><i class="fas fa-times-circle"></i> Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280;">Last Login</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('M d, Y H:i A') }}
                            @else
                                First Login
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>
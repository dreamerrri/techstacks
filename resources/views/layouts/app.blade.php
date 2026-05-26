<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - HR Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
        }

        .sidebar {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            min-height: 100vh;
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        .sidebar-header h1 { font-size: 24px; margin-bottom: 5px; }
        .sidebar-header p  { font-size: 12px; opacity: 0.8; }

        .nav-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.3);
            border-left: 4px solid #fbbf24;
        }

        .nav-item i { width: 20px; text-align: center; }

        .logout-btn {
            width: calc(100% - 40px);
            margin: 0 20px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }

        .logout-btn:hover { background: rgba(255, 255, 255, 0.3); }

        .topbar {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: bold;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background: #fecaca;
            color: #991b1b;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card h2 { color: #1f2937; margin-bottom: 20px; font-size: 22px; }

        .content { padding: 40px; }
    </style>
</head>
<body>
<div style="display: grid; grid-template-columns: 250px 1fr; min-height: 100vh;">

    {{-- Sidebar --}}
    <div class="sidebar" style="position: relative; display: flex; flex-direction: column;">
        <div class="sidebar-header">
            <h1>HR System</h1>
            <p>Admin Portal</p>
        </div>

        <nav style="flex: 1;">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#"
               class="nav-item">
                <i class="fas fa-users"></i>
                <span>All Users</span>
            </a>
            <a href="{{ route('employees.index') }}"
               class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i>
                <span>Manage Staff</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-lock"></i>
                <span>Access Control</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-shield-alt"></i>
                <span>System Security</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cogs"></i>
                <span>Settings</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-file-alt"></i>
                <span>Audit Logs</span>
            </a>
        </nav>

        <div style="padding-bottom: 20px;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="display: flex; flex-direction: column;">
        {{-- Topbar --}}
        <div class="topbar">
            <h2 style="margin: 0; color: #1f2937;">@yield('title')</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; color:#1f2937;">{{ auth()->user()->name }}</div>
                    <div style="font-size:12px; color:#6b7280;">Administrator</div>
                </div>
                <span class="role-badge">Admin</span>
            </div>
        </div>

        {{-- Page Content --}}
        <div class="content">
            @if(session('success'))
                <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

</div>
</body>
</html>
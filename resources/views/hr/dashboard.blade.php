<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard - HR Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
        }

        .sidebar {
            background: linear-gradient(135deg, #2dd4bf 0%, #a7f3d0 100%);
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

        .sidebar-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        .nav-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 10px;
            border-radius: 5px;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.3);
            border-left: 4px solid #fbbf24;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .topbar {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2dd4bf 0%, #a7f3d0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #065f46;
            font-weight: bold;
        }

        .user-details h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .user-details p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background: #d1fae5;
            color: #065f46;
        }

        .content {
            padding: 40px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #1f2937;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }

        .welcome-message {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .hr-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div style="display: grid; grid-template-columns: 250px 1fr; min-height: 100vh;">
        <!-- Sidebar -->
        <div class="sidebar" style="position: relative;">
            <div class="sidebar-header">
                <h1>HR System</h1>
                <p>HR Portal</p>
            </div>

            <nav>
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-money-bill"></i>
                    <span>Payroll</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Attendance</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-suitcase"></i>
                    <span>Leave Requests</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </div>
            </nav>

            <form action="{{ route('logout') }}" method="POST" style="position: absolute; bottom: 0; width: 100%;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div>
            <!-- Topbar -->
            <div class="topbar">
                <h2 style="margin: 0; color: #1f2937;">HR Dashboard</h2>
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-details">
                        <h3>{{ auth()->user()->name }}</h3>
                        <p>HR Personnel</p>
                    </div>
                    <span class="role-badge">HR</span>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="hr-badge">
                    <i class="fas fa-user-tie"></i> HR Department Access
                </div>

                <div class="welcome-message">
                    <i class="fas fa-wave-hand" style="color: #2563eb;"></i> Welcome, {{ auth()->user()->name }}! You have HR access privileges.
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #2563eb;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value">245</div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #1e40af;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-value">238</div>
                        <div class="stat-label">Present Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #fbbf24;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-value">12</div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #10b981;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-value">7</div>
                        <div class="stat-label">On Leave Today</div>
                    </div>
                </div>

                <div class="card">
                    <h2>HR Actions</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <button style="padding: 15px; border: 2px solid #2563eb; background: white; color: #2563eb; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Add Employee')">
                            <i class="fas fa-user-plus"></i> Add Employee
                        </button>
                        <button style="padding: 15px; border: 2px solid #2563eb; background: white; color: #2563eb; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Process Payroll')">
                            <i class="fas fa-calculator"></i> Payroll
                        </button>
                        <button style="padding: 15px; border: 2px solid #2563eb; background: white; color: #2563eb; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Leave Requests')">
                            <i class="fas fa-inbox"></i> Leave Requests
                        </button>
                        <button style="padding: 15px; border: 2px solid #2563eb; background: white; color: #2563eb; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Generate Reports')">
                            <i class="fas fa-file-pdf"></i> Reports
                        </button>
                    </div>
                </div>

                <div class="card">
                    <h2>System Information</h2>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 10px 0; color: #6b7280;">Name</td>
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ auth()->user()->name }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 10px 0; color: #6b7280;">Email</td>
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ auth()->user()->email }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 10px 0; color: #6b7280;">Role</td>
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">HR Personnel</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 10px 0; color: #6b7280;">Account Status</td>
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                                @if(auth()->user()->is_active)
                                    <span style="color: #10b981;">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span style="color: #dc2626;">
                                        <i class="fas fa-times-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: #6b7280;">Last Login</td>
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                                @if(auth()->user()->last_login_at)
                                    {{ auth()->user()->last_login_at->format('M d, Y H:i A') }}
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

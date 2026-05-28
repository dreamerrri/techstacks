@extends('layouts.app')

<<<<<<< HEAD
        .sidebar {
            background: linear-gradient(135deg, #22ce9d 0%, #a6f3e0 100%);
            color: white;
            min-height: 100vh;
            padding: 20px 0;
        }
=======
@section('title')
    @if($user->role === 'admin') Admin Dashboard
    @elseif($user->role === 'hr') HR Dashboard
    @else Dashboard
    @endif
@endsection
>>>>>>> 41e6c0d28a9469a2871a765e3f245f872eebd9e8

@section('content')

@php
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
    $color   = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
@endphp

{{-- Role access badge --}}
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

<<<<<<< HEAD
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
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(34, 206, 157, 0.7);
            color: #064e3b;
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
            background: linear-gradient(135deg, #22ce9d 0%, #a6f3e0 100%);
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
        }

        .role-admin {
            background: #d9f8ef;
            color: #064e3b;
        }

        .role-hr {
            background: #d9f8ef;
            color: #064e3b;
        }

        .role-employee {
            background: #d9f8ef;
            color: #064e3b;
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
    </style>
</head>
<body>
    <div style="display: grid; grid-template-columns: 250px 1fr; min-height: 100vh;">
        <!-- Sidebar -->
        <div class="sidebar" style="position: relative;">
            <div class="sidebar-header">
                <h1>HR System</h1>
                <p>Management Portal</p>
=======
{{-- Stats --}}
<div class="stats-grid">
    @foreach($stats as $stat)
        <div class="stat-card">
            <div class="stat-icon" style="color: {{ $stat['color'] }};">
                <i class="fas {{ $stat['icon'] }}"></i>
>>>>>>> 41e6c0d28a9469a2871a765e3f245f872eebd9e8
            </div>
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="stat-label">{{ $stat['label'] }}</div>
        </div>
    @endforeach
</div>

<<<<<<< HEAD
        <!-- Main Content -->
        <div>
            <!-- Topbar -->
            <div class="topbar">
                <h2 style="margin: 0; color: #1f2937;">Dashboard</h2>
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-details">
                        <h3>{{ auth()->user()->name }}</h3>
                        <p>{{ ucfirst(auth()->user()->role) }} User</p>
                    </div>
                    <span class="role-badge role-{{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="welcome-message">
                    <i class="fas fa-wave-hand" style="color: #667eea;"></i> Welcome, {{ auth()->user()->name }}!
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #22ce9d;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value">245</div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #764ba2;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-value">238</div>
                        <div class="stat-label">Present Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #fbbf24;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-value">$2.5M</div>
                        <div class="stat-label">Monthly Payroll</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #10b981;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">+12%</div>
                        <div class="stat-label">Growth Rate</div>
                    </div>
                </div>

                <div class="card">
                    <h2>Quick Actions</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <button style="padding: 15px; border: 2px solid #22ce9d; background: white; color: #22ce9d; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Add Employee')">
                            <i class="fas fa-user-plus"></i> Add Employee
                        </button>
                        <button style="padding: 15px; border: 2px solid #22ce9d; background: white; color: #22ce9d; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Process Payroll')">
                            <i class="fas fa-calculator"></i> Process Payroll
                        </button>
                        <button style="padding: 15px; border: 2px solid #22ce9d; background: white; color: #22ce9d; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('View Reports')">
                            <i class="fas fa-file-alt"></i> View Reports
                        </button>
                        <button style="padding: 15px; border: 2px solid #22ce9d; background: white; color: #22ce9d; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onclick="alert('Manage Leaves')">
                            <i class="fas fa-calendar-times"></i> Manage Leaves
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
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ ucfirst(auth()->user()->role) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 10px 0; color: #6b7280;">Account Status</td>
                            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                                @if(auth()->user()->is_active)
                                    <span style="color: #22ce9d;">
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
=======
{{-- Quick Actions --}}
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
>>>>>>> 41e6c0d28a9469a2871a765e3f245f872eebd9e8
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

@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - HR Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 60px;
            text-align: center;
            max-width: 600px;
            width: 90%;
        }

        .error-icon {
            font-size: 72px;
            color: #dc2626;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 36px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: 700;
        }

        p {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 14px;
        }

        .btn btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn btn-secondary:hover {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1>Access Denied</h1>
        <p>You do not have permission to access this page. Your current role does not grant you access to this resource. Please contact your administrator if you believe this is an error.</p>
        <div class="flex gap-2">
            <a href="/dashboard" class="btn btn btn-primary">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
            <a href="/logout" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();" class="btn btn btn-secondary">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>
</body>
</html>

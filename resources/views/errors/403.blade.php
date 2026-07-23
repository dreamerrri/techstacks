<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - HR Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-6">
    <div class="card bg-base-100 border border-base-300 shadow-xl text-center w-full max-w-xl p-8 sm:p-14">
        <div class="text-error text-6xl mb-5">
            <i class="icon-[ph--lock-fill]"></i>
        </div>
        <h1 class="text-3xl font-bold text-base-content mb-3">Access Denied</h1>
        <p class="text-base-content/60 text-base leading-relaxed mb-8">
            You do not have permission to access this page. Your current role does not grant you access to this resource. Please contact your administrator if you believe this is an error.
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="icon-[ph--house-fill]"></i> Go to Dashboard
            </a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();" class="btn btn-outline">
                <i class="icon-[ph--sign-out-fill]"></i> Logout
            </a>
        </div>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</body>
</html>

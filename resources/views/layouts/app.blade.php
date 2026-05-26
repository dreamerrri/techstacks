<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - LogiPay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow mb-6">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <span class="font-bold text-blue-600 text-lg">LogiPay HR</span>
            <a href="/employees" class="text-sm text-gray-600 hover:text-blue-600">Employees</a>
        </div>
    </nav>

    @yield('content')

</body>
</html>
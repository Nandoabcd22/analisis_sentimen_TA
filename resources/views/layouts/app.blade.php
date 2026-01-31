<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Analisis Sentimen</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #1a1a1a;
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 0 40px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .navbar-brand {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            text-decoration: none;
        }
        
        .navbar-menu {
            display: flex;
            gap: 40px;
            align-items: center;
            list-style: none;
        }
        
        .navbar-menu a {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .navbar-menu a:hover,
        .navbar-menu a.active {
            color: #0066cc;
        }
        
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 70px);
            padding: 60px 20px;
        }
        
        .header-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.4;
            margin-bottom: 15px;
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .logo-container img {
            width: 200px;
            height: 200px;
            margin: 30px 0;
        }
        
        .content {
            width: 100%;
            max-width: 1200px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">ANALISIS SENTIMEN</a>
        <ul class="navbar-menu">
            <li><a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard')) active @endif">Dashboard</a></li>
            <li><a href="{{ route('preprocessing') }}" class="@if(request()->routeIs('preprocessing')) active @endif">Preprocessing</a></li>
            <li><a href="{{ route('klasifikasi') }}" class="@if(request()->routeIs('klasifikasi')) active @endif">Klasifikasi</a></li>
            <li><a href="{{ route('hasil-laporan') }}" class="@if(request()->routeIs('hasil-laporan')) active @endif">Hasil Dan Laporan</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>

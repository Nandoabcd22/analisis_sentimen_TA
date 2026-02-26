<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title'); ?> - Analisis Sentimen</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
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

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .user-name {
            font-size: 13px;
            color: #333;
            font-weight: 500;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #2196F3 100%;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .auth-links {
            display: flex;
            gap: 15px;
        }

        .auth-links a {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .auth-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
        <a href="<?php echo e(route('dashboard')); ?>" class="navbar-brand">ANALISIS SENTIMEN</a>
        <ul class="navbar-menu">
            <?php if(auth()->guard()->check()): ?>
                <li><a href="<?php echo e(route('dashboard')); ?>" class="<?php if(request()->routeIs('dashboard')): ?> active <?php endif; ?>">Dashboard</a></li>
                <li><a href="<?php echo e(route('preprocessing')); ?>" class="<?php if(request()->routeIs('preprocessing')): ?> active <?php endif; ?>">Preprocessing</a></li>
                <li><a href="<?php echo e(route('tfidf')); ?>" class="<?php if(request()->routeIs('tfidf')): ?> active <?php endif; ?>">TF-IDF</a></li>
                <li><a href="<?php echo e(route('klasifikasi')); ?>" class="<?php if(request()->routeIs('klasifikasi')): ?> active <?php endif; ?>">Klasifikasi</a></li>
            <?php endif; ?>
        </ul>
        
        <?php if(auth()->guard()->check()): ?>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-avatar"><?php echo e(substr(Auth::user()->name, 0, 1)); ?></div>
                    <div class="user-name"><?php echo e(Auth::user()->name); ?></div>
                </div>
                <form action="<?php echo e(route('logout')); ?>" method="POST" style="margin: 0; flex-shrink: 0;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout-btn">🚪 Logout</button>
                </form>
            </div>
        <?php else: ?>
            <div class="auth-links">
                <a href="<?php echo e(route('login')); ?>">Login</a>
                <a href="<?php echo e(route('register')); ?>">Register</a>
            </div>
        <?php endif; ?>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</body>
</html>
<?php /**PATH D:\laragon\www\analisis_sentimen_TA\resources\views/layouts/app.blade.php ENDPATH**/ ?>
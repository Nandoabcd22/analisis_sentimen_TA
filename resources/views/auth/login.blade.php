<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Analisis Sentimen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color:2 #2196F3196F3;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.77);
        }

        .form-input::placeholder {
            color: #aaa;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .remember-forgot a {
            color: #2196F3;
            text-decoration: none;
            transition: color 0.3s;
        }

        .remember-forgot a:hover {
            color: #2196F3;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #2196F3 100%;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #3c3;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }

        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            color: #764ba2;
        }

        .errors-list {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .errors-list ul {
            margin: 0;
            padding-left: 20px;
        }

        .errors-list li {
            color: #c33;
            margin-bottom: 5px;
        }

        .errors-list li:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Login</h1>
            <p>Masuk ke akun Anda</p>
        </div>

        @if ($errors->any())
            <div class="errors-list">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" 
                       placeholder="nama@email.com" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" 
                       placeholder="Masukkan password" required>
            </div>

            <div class="remember-forgot">
                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    <label for="remember">Ingat saya</label>
                </div>
                <a href="#">Lupa password?</a>
            </div>

            <button type="submit" class="login-btn">🔓 Login</button>
        </form>

        <div class="form-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
        </div>
    </div>
</body>
</html>

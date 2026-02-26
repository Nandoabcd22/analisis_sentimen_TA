<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Analisis Sentimen</title>
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

        .register-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
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

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .register-header p {
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
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-input::placeholder {
            color: #aaa;
        }

        .form-input.is-invalid {
            border-color: #c33;
        }

        .error-text {
            font-size: 12px;
            color: #c33;
            margin-top: 4px;
            display: block;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 6px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            border-left: 3px solid #667eea;
        }

        .register-btn {
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
            margin-top: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
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
            color: #2196F3;
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
    <div class="register-container">
        <div class="register-header">
            <h1>📝 Register</h1>
            <p>Buat akun baru Anda</p>
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

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" 
                       placeholder="Nama lengkap Anda" value="{{ old('name') }}" required>
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" 
                       placeholder="nama@email.com" value="{{ old('email') }}" required>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input @error('password') is-invalid @enderror" 
                       placeholder="Minimal 8 karakter" required>
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
                <div class="password-requirements">
                    <strong>Persyaratan:</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                        <li>Minimal 8 karakter</li>
                        <li>Kombinasi huruf besar, kecil, angka, dan simbol</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-input" 
                       placeholder="Ulangi password Anda" required>
            </div>

            <button type="submit" class="register-btn">✨ Buat Akun</button>
        </form>

        <div class="form-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
    </div>
</body>
</html>

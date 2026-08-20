<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZKIOS - Professional POS & Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(-45deg, #0f172a, #1e293b, #334155, #0f172a);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Abstract shapes in background */
        .shape {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
        }
        .shape-1 {
            width: 400px; height: 400px;
            background: #3b82f6;
            top: -100px; left: -100px;
            border-radius: 50%;
        }
        .shape-2 {
            width: 500px; height: 500px;
            background: #8b5cf6;
            bottom: -150px; right: -100px;
            border-radius: 50%;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 50px) scale(1.1); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 2rem;
            z-index: 1;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            color: #fff;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-header h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            letter-spacing: -1px;
            margin: 0;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-header p {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-top: 0.5rem;
        }

        .form-floating > .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.15);
        }

        .form-floating > label {
            color: #94a3b8;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #60a5fa;
            transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem);
        }

        .btn-login {
            background: linear-gradient(to right, #3b82f6, #6366f1);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.5);
            background: linear-gradient(to right, #2563eb, #4f46e5);
            color: #fff;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-accounts {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .demo-role {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            transition: background 0.3s ease;
            cursor: pointer;
        }

        .demo-role:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .demo-role .role-name {
            font-weight: 600;
            color: #e2e8f0;
        }

        .copyright {
            text-align: center;
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="login-wrapper">
        <div class="glass-card">
            <div class="brand-header">
                <i class="bi bi-shop-window" style="font-size: 3rem; background: linear-gradient(to right, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                <h1 class="mt-2">EZKIOS</h1>
                <p>Sistem Manajemen & Kasir Digital</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #fca5a5; border-radius: 12px; font-size: 0.9rem;">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-floating mb-4">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-2"></i>Alamat Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="bi bi-shield-lock me-2"></i>Kata Sandi</label>
                </div>

                <button type="submit" class="btn-login">
                    LOGIN <i class="bi bi-arrow-right-short ms-1 fs-5 align-middle"></i>
                </button>
            </form>

            {{-- <div class="demo-accounts">
                <div class="text-center mb-3">
                    <i class="bi bi-person-badge" style="color: #a78bfa;"></i> <span class="fw-medium text-light">Akses Akun Demo</span>
                </div>
                <div class="demo-role" onclick="fillDemo('admin@ezkios.com')">
                    <span class="role-name">Administrator</span>
                    <span>admin@ezkios.com</span>
                </div>
                <div class="demo-role" onclick="fillDemo('kasir@ezkios.com')">
                    <span class="role-name">Kasir (POS)</span>
                    <span>kasir@ezkios.com</span>
                </div>
                <div class="demo-role" onclick="fillDemo('owner@ezkios.com')">
                    <span class="role-name">Pemilik (Owner)</span>
                    <span>owner@ezkios.com</span>
                </div>
                <div class="text-center mt-2">
                    <small style="color: #64748b;">*Klik salah satu akun di atas untuk login otomatis</small>
                </div>
            </div> --}}
        </div>

        <div class="copyright">
            &copy; {{ date('Y') }} EZKIOS POS System. Crafted for Retail.
        </div>
    </div>

    <script>
        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
            // Optional: Auto-submit form for seamless demo access
            // document.querySelector('form').submit();
        }
    </script>
</body>
</html>

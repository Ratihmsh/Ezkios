<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZKIOS - Professional POS & Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .login-container {
            min-height: 100vh;
        }

        /* Hero / Visual Side */
        .brand-side {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.9), rgba(15, 23, 42, 0.95)),
                        url('https://images.unsplash.com/photo-1556742049-0a670f4a4591?q=80&w=1200&auto=format&fit=crop') center/cover;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 4rem;
        }

        .brand-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.3), transparent 70%);
            pointer-events: none;
        }

        /* Form Side */
        .form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            background-color: var(--bg-dark);
        }

        .form-wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* Floating Label Custom Styling */
        .form-floating > .form-control {
            background-color: #1e293b; /* Solid dark color */
            border: 1px solid #334155;
            color: #f8fafc;
            border-radius: 12px;
            font-size: 0.95rem;
        }

        .form-floating > .form-control:focus {
            background-color: #0f172a; /* Slightly darker on focus */
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
            color: #f8fafc;
        }

        /* Fix Chrome autofill styling in dark mode */
        .form-floating > .form-control:-webkit-autofill,
        .form-floating > .form-control:-webkit-autofill:hover,
        .form-floating > .form-control:-webkit-autofill:focus,
        .form-floating > .form-control:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #1e293b inset !important;
            -webkit-text-fill-color: #f8fafc !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .form-floating > label {
            color: #64748b; /* Better contrast for label */
        }

        /* Remove Bootstrap's default white background behind floating labels */
        .form-floating > label::after {
            background-color: transparent !important;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #60a5fa;
        }

        /* Buttons & Actions */
        .btn-login {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.4);
            color: #fff;
        }

        /* Demo Cards */
        .demo-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .demo-card:hover {
            background: rgba(30, 41, 59, 1);
            border-color: #475569;
            transform: translateX(4px);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            cursor: pointer;
            color: var(--text-muted);
        }

        .password-toggle:hover {
            color: #fff;
        }

        @media (max-width: 991.98px) {
            .brand-side {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0 login-container">

            <!-- Left Side: Branding & Info -->
            <div class="col-lg-6 brand-side">
                <div class="position-relative z-1">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="bg-primary text-white p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-shop-window fs-4"></i>
                        </div>
                        <span class="fs-4 fw-bold text-white tracking-wide">EZKIOS</span>
                    </div>
                </div>

                <div class="position-relative z-1 my-auto">
                    <h1 class="display-5 fw-bold text-white mb-3">Kelola Usaha Ritel Jadi Lebih Praktis.</h1>
                    <p class="text-light opacity-75 fs-5">Sistem POS modern yang cepat, terintegrasi, dan mudah digunakan untuk mendukung pertumbuhan bisnis Anda.</p>
                </div>

                <div class="position-relative z-1">
                    <div class="d-flex gap-4 text-light opacity-75 small">
                        <span><i class="bi bi-check-circle-fill text-primary me-2"></i>Real-time Report</span>
                        <span><i class="bi bi-check-circle-fill text-primary me-2"></i>Multi User</span>
                        <span><i class="bi bi-check-circle-fill text-primary me-2"></i>Cloud Backup</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="col-lg-6 form-side">
                <div class="form-wrapper">

                    <!-- Mobile Logo Only -->
                    <div class="d-lg-none text-center mb-4">
                        <div class="bg-primary text-white p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-shop-window fs-2"></i>
                        </div>
                        <h2 class="fw-bold text-white mb-0">EZKIOS</h2>
                    </div>

                    <div class="mb-4">
                        <h3 class="fw-bold text-white mb-1">Selamat Datang</h3>
                        <p class="text-white small">Masukan kredensial akun Anda untuk mengakses sistem.</p>
                    </div>

                    <!-- Alert Errors -->
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-center rounded-3 py-2 px-3 mb-4" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <div class="small">{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <!-- Email Input -->
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                            <label for="email"><i class="bi bi-envelope me-2"></i>Alamat Email</label>
                        </div>

                        <!-- Password Input -->
                        <div class="form-floating mb-4 position-relative">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock me-2"></i>Kata Sandi</label>
                            <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-login mb-4">
                            Masuk ke Sistem <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <!-- Demo Account Selector -->
                    <div class="pt-3 border-top border-secondary border-opacity-25">
                        <p class="text-white small mb-2 fw-medium"><i class="bi bi-person-badge me-1"></i> Akun Demo (Klik untuk isi otomatis):</p>

                        <div class="demo-card d-flex justify-content-between align-items-center" onclick="fillDemo('admin@ezkios.com')">
                            <div>
                                <div class="fw-semibold text-white small">Administrator</div>
                                <div class="text-white text-xs" style="font-size: 0.75rem;">admin@ezkios.com</div>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Admin</span>
                        </div>

                        <div class="demo-card d-flex justify-content-between align-items-center" onclick="fillDemo('kasir@ezkios.com')">
                            <div>
                                <div class="fw-semibold text-white small">Kasir (POS)</div>
                                <div class="text-white text-xs" style="font-size: 0.75rem;">kasir@ezkios.com</div>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Kasir</span>
                        </div>

                        <div class="demo-card d-flex justify-content-between align-items-center" onclick="fillDemo('owner@ezkios.com')">
                            <div>
                                <div class="fw-semibold text-white small">Pemilik (Owner)</div>
                                <div class="text-white text-xs" style="font-size: 0.75rem;">owner@ezkios.com</div>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">Owner</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <small class="text-white" style="font-size: 0.75rem;">&copy; {{ date('Y') }} EZKIOS POS System. All rights reserved.</small>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        // Fill Demo Credentials
        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
        }

        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>

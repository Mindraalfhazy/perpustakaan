<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Perpustakaan MTsN 3 Bone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --app-primary: #4F46E5;
            --app-primary-hover: #4338CA;
            --app-primary-soft: #EEF2FF;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: #F8FAFC;
            color: #0F172A;
            display: flex;
            align-items: center;
        }
        .login-shell { min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .login-panel {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
            background: #fff;
            max-width: 1100px;
            width: 100%;
        }
        .brand-panel {
            background: linear-gradient(135deg, #1E1B4B 0%, #4338CA 100%);
            position: relative;
            color: #fff;
            padding: 60px;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 20% 150%, rgba(255,255,255,0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% -50%, rgba(99, 102, 241, 0.4) 0%, transparent 50%);
            pointer-events: none;
        }
        .brand-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: rgba(255, 255, 255, 1);
            border-radius: 20px;
            padding: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        .form-panel { background: #fff; padding: 60px; }
        .form-control { 
            min-height: 52px; 
            border-radius: 12px; 
            border: 1px solid #E2E8F0;
            padding: 12px 16px;
            font-weight: 500;
            background-color: #F8FAFC;
            transition: all 0.2s;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: var(--app-primary);
            box-shadow: 0 0 0 4px var(--app-primary-soft);
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }
        .btn-login {
            min-height: 52px;
            border-radius: 12px;
            background: var(--app-primary);
            border-color: var(--app-primary);
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: var(--app-primary-hover);
            border-color: var(--app-primary-hover);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }
        @media (max-width: 991.98px) {
            .brand-panel, .form-panel { padding: 40px 30px; }
            .login-shell { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="card login-panel">
            <div class="row g-0 h-100">
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="brand-panel h-100 d-flex flex-column justify-content-between">
                        <div style="position: relative; z-index: 1;">
                            <img src="{{ asset('logo/kemenag.png') }}" alt="Logo MTsN 3 Bone" class="brand-logo mb-5">
                            <div class="small text-uppercase fw-bold mb-3" style="letter-spacing:0.15em; color: #A5B4FC;">Perpustakaan Digital</div>
                            <h1 class="display-5 fw-bolder mb-4" style="line-height: 1.1;">MTsN 3 Bone</h1>
                            <p class="mb-0 text-white-50 fs-5" style="line-height: 1.6;">Akses literasi digital tak terbatas. Membaca, meminjam, dan mengelola buku menjadi lebih mudah dan elegan.</p>
                        </div>
                        <div class="mt-5 pt-5 small" style="color: #818CF8; position: relative; z-index: 1;">&copy; {{ date('Y') }} MTsN 3 Bone. All rights reserved.</div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="form-panel h-100 d-flex flex-column justify-content-center">
                        <div class="mb-5">
                            <div class="d-lg-none mb-4">
                                <img src="{{ asset('logo/kemenag.png') }}" alt="Logo MTsN 3 Bone" width="60" class="mb-3 rounded-4 shadow-sm">
                            </div>
                            <div class="text-primary text-uppercase small fw-bold mb-2" style="letter-spacing:0.1em; color: var(--app-primary) !important;">Selamat Datang</div>
                            <h2 class="h2 fw-bolder mb-2" style="color: #0F172A;">Login ke akun Anda</h2>
                            <p class="text-muted mb-0 fs-6">Silakan masuk menggunakan kredensial yang terdaftar.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 mb-4">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST" class="row g-4">
                            @csrf
                            <div class="col-12">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@sekolah.sch.id" required autofocus>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="password" class="form-label mb-0">Password</label>
                                </div>
                                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-login w-100">Masuk ke Dashboard</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

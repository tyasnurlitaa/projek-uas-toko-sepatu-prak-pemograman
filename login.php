<?php
require_once __DIR__ . '/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (loginUser($username, $password)) {
        header('Location: index.php');
        exit;
    }

    $error = 'Username atau password salah.';
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Sepatu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-canvas: #FDFBF7; /* Nude Krim Sangat Lembut */
            --bg-gradient-start: #FAF5EF;
            --bg-gradient-end: #EAE0D5; /* Pasir Hangat */
            --card-bg: rgba(255, 255, 255, 0.85); /* Putih Kaca Lembut */
            --border-color: rgba(127, 85, 57, 0.12); /* Garis Cokelat Tipis */
            --primary-nude: #7F5539; /* Cokelat Espresso Hangat */
            --primary-grad: linear-gradient(135deg, #B5828F 0%, #7F5539 100%); /* Nude Rose ke Espresso */
            --text-primary: #4A3B32; /* Cokelat Gelap untuk Keterbacaan Tinggi */
            --text-secondary: #9C8979; /* Cokelat Pasir Muted */
            --accent-glow: rgba(181, 130, 143, 0.15);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Dekorasi Cahaya Organik Estetis (Organic Nude Glow) */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.6;
            pointer-events: none;
            animation: floatGlow 14s ease-in-out infinite alternate;
        }

        body::before {
            background: #DDB892; /* Warm Apricot Nude */
            top: 5%;
            left: 5%;
        }

        body::after {
            background: #B5828F; /* Soft Rose Nude */
            bottom: 5%;
            right: 5%;
            animation-delay: 3s;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 30px) scale(1.15); }
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 24px;
            z-index: 1;
            position: relative;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            box-shadow: 
                0 20px 40px -15px rgba(127, 85, 57, 0.08), 
                0 0 50px rgba(181, 130, 143, 0.05);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .login-card:hover {
            transform: translateY(-4px);
            border-color: rgba(127, 85, 57, 0.25);
            box-shadow: 
                0 30px 50px -10px rgba(127, 85, 57, 0.12), 
                0 0 60px rgba(181, 130, 143, 0.12);
        }

        .brand-logo-container {
            width: 68px;
            height: 68px;
            background: #E6CCB2; /* Soft sand */
            background-image: linear-gradient(135deg, #EDE0D4 0%, #DDB892 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            box-shadow: 0 8px 20px rgba(127, 85, 57, 0.08);
            border: 2px solid #FFFFFF;
        }

        .brand-logo-container i {
            font-size: 1.8rem;
            color: #7F5539;
        }

        .header-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-group-custom {
            position: relative;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(127, 85, 57, 0.15);
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
        }

        .input-group-custom:focus-within {
            border-color: #B5828F; /* Soft Rose Highlight */
            box-shadow: 0 0 0 4px rgba(181, 130, 143, 0.2);
            background: #FFFFFF;
        }

        .input-group-custom i.input-icon {
            padding-left: 18px;
            color: var(--text-secondary);
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .input-group-custom:focus-within i.input-icon {
            color: #7F5539;
        }

        .input-group-custom .form-control {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: var(--text-primary) !important;
            padding: 14px 16px;
            font-size: 0.95rem;
            width: 100%;
        }

        .input-group-custom .form-control::placeholder {
            color: #C6C1B9;
        }

        /* Chrome Autocomplete Fix */
        .input-group-custom .form-control:-webkit-autofill {
            -webkit-text-fill-color: var(--text-primary) !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .btn-toggle-password {
            background: none;
            border: none;
            color: var(--text-secondary);
            padding-right: 18px;
            transition: color 0.2s;
            cursor: pointer;
        }

        .btn-toggle-password:hover {
            color: var(--text-primary);
        }

        .btn-submit {
            background: var(--primary-grad);
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            color: white;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(127, 85, 57, 0.15);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(127, 85, 57, 0.25);
            opacity: 0.95;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* Notifikasi Error */
        .alert-custom {
            background: rgba(230, 92, 92, 0.08);
            border: 1px solid rgba(230, 92, 92, 0.15);
            color: #9C3A3A;
            border-radius: 14px;
            font-size: 0.85rem;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Info Box Akun Bawaan */
        .info-box {
            background: rgba(127, 85, 57, 0.03);
            border-radius: 14px;
            border: 1px dashed rgba(127, 85, 57, 0.15);
            padding: 14px;
        }

        .info-box code {
            color: #7F5539;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="card-body p-4 p-sm-5 text-center">
            
            <!-- Logo Estetik Minimalis -->
            <div class="brand-logo-container">
                <i class="bi bi-layers-half"></i>
            </div>
            
            <h3 class="header-title">TOKO SEPATU</h3>
            <p class="header-subtitle mb-4">Masuk ke Dashboard Manajemen Admin</p>
            
            <!-- Notifikasi Error jika login gagal -->
            <?php if ($error): ?>
                <div class="alert alert-custom mb-4 text-start animate__animated animate__headShake" role="alert">
                    <i class="bi bi-shield-exclamation fs-5"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>
            
            <!-- Form Login -->
            <form method="post" autocomplete="off" class="text-start">
                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group-custom">
                        <i class="bi bi-key input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        <button type="button" class="btn-toggle-password" id="togglePasswordBtn" onclick="togglePassword()">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Tombol Masuk -->
                <button type="submit" class="btn btn-submit w-100 mb-4">
                    Sign In <i class="bi bi-arrow-right-short fs-5 ms-1 align-middle"></i>
                </button>
            </form>
            
            <!-- Hint Akun Default -->
            <div class="info-box">
                <span class="text-muted d-block small mb-1" style="font-size: 0.75rem;">Akses demo masuk sistem:</span>
                <code class="small" style="background: transparent; padding: 0;">admin / admin123</code>
            </div>
            
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }
</script>
</body>
</html>
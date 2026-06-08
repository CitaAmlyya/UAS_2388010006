<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password tidak boleh kosong!";
    } else {
        // FIX: Gunakan prepared statement untuk mencegah SQL injection
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                // Set Session
                $_SESSION['login']    = true;
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email']    = $row['email'];
                $_SESSION['role']     = $row['role'];

                if ($row['role'] == 'admin') {
                    header("Location: admin/index.php");
                    exit;
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak terdaftar!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Novelib (Perpustakaan Novel)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-900 min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Background Elements -->
    <div class="absolute w-96 h-96 -top-20 -left-20 bg-indigo-500/10 rounded-full blur-3xl"></div>
    <div class="absolute w-96 h-96 -bottom-20 -right-20 bg-purple-500/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-md bg-white/5 backdrop-blur-xl border border-white/10 p-8 rounded-2xl shadow-2xl relative z-10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 text-3xl mb-4 shadow-inner">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide">NoveLib</h1>
            <p class="text-sm text-slate-400 mt-2">Masuk untuk menjelajahi dunia imajinasi</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" required
                        class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Masukkan username">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        class="w-full pl-10 pr-10 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Masukkan password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                        <i id="eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login"
                class="w-full py-3.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 active:scale-[0.98] shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/35 transition-all">
                Masuk Ke Perpustakaan
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-white/5">
            <p class="text-sm text-slate-400">
                Belum punya akun? 
                <a href="register.php" class="text-indigo-400 font-semibold hover:underline">Daftar Anggota</a>
                <p class="text-gray-400 text-sm mt-2">Dibuat oleh: Cita Amelia 2388010006</p>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-regular', 'fa-eye');
                eyeIcon.classList.add('fa-solid', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-solid', 'fa-eye-slash');
                eyeIcon.classList.add('fa-regular', 'fa-eye');
            }
        }
    </script>
</body>
</html>

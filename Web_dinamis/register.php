<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, redirect
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error   = "";
$success = "";

if (isset($_POST['register'])) {
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password harus minimal 6 karakter!";
    } else {
        // FIX: Gunakan prepared statement untuk cek duplikat
        $stmt = mysqli_prepare($koneksi, "SELECT username, email FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $row = mysqli_fetch_assoc($check_result);
            if ($row['username'] === $username) {
                $error = "Username sudah terdaftar!";
            } else {
                $error = "Email sudah terdaftar!";
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // FIX: Gunakan prepared statement untuk insert
            $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'anggota')");
            mysqli_stmt_bind_param($stmt_insert, "sss", $username, $hashed_password, $email);

            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan sistem, pendaftaran gagal.";
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - NoveLib</title>
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
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Daftar Anggota</h1>
            <p class="text-sm text-slate-400 mt-2">Buat akun untuk meminjam novel favorit Anda</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-base shrink-0"></i>
                <span><?= htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-1.5">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Pilih username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="contoh@domain.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Minimal 6 karakter">
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-slate-300 mb-1.5">Konfirmasi Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="confirm_password" name="confirm_password" required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Ulangi password">
                </div>
            </div>

            <button type="submit" name="register"
                class="w-full py-3.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 active:scale-[0.98] shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/35 transition-all">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center mt-6 pt-6 border-t border-white/5">
            <p class="text-sm text-slate-400">
                Sudah punya akun? 
                <a href="login.php" class="text-indigo-400 font-semibold hover:underline">Masuk di sini</a>
            </p>
        </div>
    </div>
</body>
</html>

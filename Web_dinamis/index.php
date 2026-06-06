<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek jika yang login adalah admin, redirect ke dashboard admin
if ($_SESSION['role'] == 'admin') {
    header("Location: admin/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Search & Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';
$genre_filter = isset($_GET['genre']) ? mysqli_real_escape_string($koneksi, trim($_GET['genre'])) : '';

// Build Query
$query = "SELECT * FROM novels WHERE 1=1";
if (!empty($search)) {
    $query .= " AND (judul LIKE '%$search%' OR penulis LIKE '%$search%')";
}
if (!empty($genre_filter)) {
    $query .= " AND genre = '$genre_filter'";
}
$query .= " ORDER BY id DESC";
$novels_result = mysqli_query($koneksi, $query);

// Get All Genres for filtering
$genres_query = "SELECT DISTINCT genre FROM novels";
$genres_result = mysqli_query($koneksi, $genres_query);

// Get Active Borrows for this user
$borrows_query = "SELECT p.*, n.judul, n.penulis FROM peminjaman p 
                  JOIN novels n ON p.novel_id = n.id 
                  WHERE p.user_id = $user_id AND p.status = 'dipinjam'";
$borrows_result = mysqli_query($koneksi, $borrows_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoveLib - Perpustakaan Novel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- Navbar -->
    <nav class="sticky top-0 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <span class="text-xl font-bold tracking-wider bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">NoveLib</span>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-xs w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-slate-400">Halo, <strong class="text-slate-100"><?= htmlspecialchars($username); ?></strong></span>
                </div>
                <a href="logout.php" class="px-4 py-2 bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg text-sm font-semibold transition-all duration-300">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i>Keluar
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- User Status & Active Borrows -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="font-bold text-slate-200 text-lg mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-400"></i> Pinjaman Anda
                </h3>
                
                <?php if (mysqli_num_rows($borrows_result) > 0) : ?>
                    <div class="space-y-4">
                        <?php while ($borrow = mysqli_fetch_assoc($borrows_result)) : ?>
                            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl space-y-1">
                                <h4 class="font-semibold text-slate-200 text-sm line-clamp-1"><?= htmlspecialchars($borrow['judul']); ?></h4>
                                <p class="text-xs text-slate-500">Oleh: <?= htmlspecialchars($borrow['penulis']); ?></p>
                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-[10px] px-2 py-0.5 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-md">Dipinjam</span>
                                    <span class="text-[10px] text-slate-500">Tgl: <?= date('d M Y', strtotime($borrow['tanggal_pinjam'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="text-center py-6 text-slate-500 text-sm">
                        <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-40"></i>
                        Belum ada novel yang dipinjam.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Genre Filter -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="font-bold text-slate-200 text-lg mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-tags text-indigo-400"></i> Kategori Genre
                </h3>
                <div class="flex flex-wrap lg:flex-col gap-2">
                    <a href="index.php?search=<?= urlencode($search); ?>" 
                        class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 <?= empty($genre_filter) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 hover:bg-slate-850 border border-slate-800' ?>">
                        Semua Genre
                    </a>
                    <?php while ($genre_row = mysqli_fetch_assoc($genres_result)) : ?>
                        <?php $g = $genre_row['genre']; ?>
                        <a href="index.php?genre=<?= urlencode($g); ?>&search=<?= urlencode($search); ?>" 
                            class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 <?= $genre_filter === $g ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 hover:bg-slate-850 border border-slate-800' ?>">
                            <?= htmlspecialchars($g); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>

        </div>

        <!-- Novel Gallery -->
        <div class="lg:col-span-3 space-y-8">
            
            <!-- Search & Alerts -->
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-900 border border-slate-800 p-4 rounded-2xl shadow-xl">
                <form action="" method="GET" class="w-full relative">
                    <?php if (!empty($genre_filter)) : ?>
                        <input type="hidden" name="genre" value="<?= htmlspecialchars($genre_filter); ?>">
                    <?php endif; ?>
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
                        class="w-full pl-10 pr-24 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Cari judul novel atau penulis...">
                    <button type="submit" class="absolute right-2 top-2 px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-all">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Novel List Grid -->
            <div>
                <h2 class="text-2xl font-bold text-slate-100 mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-book text-indigo-500"></i> 
                    <?= empty($genre_filter) ? 'Semua Koleksi Novel' : 'Genre: ' . htmlspecialchars($genre_filter); ?>
                </h2>

                <?php if (mysqli_num_rows($novels_result) > 0) : ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php while ($novel = mysqli_fetch_assoc($novels_result)) : ?>
                            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-700 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <!-- Dynamic Book Cover Mockup (No placeholder images, uses beautiful HSL styled colors based on Title hash) -->
                                    <?php 
                                        $hue = abs(crc32($novel['judul'])) % 360; 
                                        $bg_style = "background: linear-gradient(135deg, hsl($hue, 70%, 20%) 0%, hsl(($hue + 60) % 360, 60%, 10%) 100%)";
                                    ?>
                                    <div style="<?= $bg_style; ?>" class="h-48 flex flex-col justify-between p-4 relative text-white border-b border-slate-800/40">
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] px-2 py-0.5 bg-white/10 backdrop-blur-md rounded-full text-slate-200 border border-white/10 uppercase tracking-wide">
                                                <?= htmlspecialchars($novel['genre']); ?>
                                            </span>
                                            <?php if ($novel['status'] === 'tersedia') : ?>
                                                <span class="text-[10px] px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full font-semibold">Tersedia</span>
                                            <?php else: ?>
                                                <span class="text-[10px] px-2 py-0.5 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full font-semibold">Dipinjam</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="font-extrabold text-lg tracking-wide drop-shadow line-clamp-2"><?= htmlspecialchars($novel['judul']); ?></h3>
                                            <p class="text-xs text-slate-300/80 italic font-medium">Oleh: <?= htmlspecialchars($novel['penulis']); ?></p>
                                        </div>
                                    </div>

                                    <div class="p-5 space-y-3">
                                        <p class="text-sm text-slate-400 line-clamp-3 leading-relaxed">
                                            <?= htmlspecialchars($novel['sinopsis']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="px-5 pb-5 pt-2 flex gap-3 border-t border-slate-800/30">
                                    <a href="detail.php?id=<?= $novel['id']; ?>" 
                                        class="flex-1 py-2 text-center bg-slate-800 hover:bg-slate-750 text-slate-200 hover:text-white text-xs font-semibold rounded-xl border border-slate-700 transition-all">
                                        Detail Novel
                                    </a>
                                    <?php if ($novel['status'] === 'tersedia') : ?>
                                        <a href="pinjam.php?id=<?= $novel['id']; ?>" 
                                            onclick="return confirm('Apakah Anda yakin ingin meminjam novel ini?')"
                                            class="flex-1 py-2 text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/10 active:scale-[0.97] transition-all">
                                            Pinjam Novel
                                        </a>
                                    <?php else: ?>
                                        <button disabled 
                                            class="flex-1 py-2 text-center bg-slate-800 text-slate-600 text-xs font-semibold rounded-xl cursor-not-allowed border border-slate-800">
                                            Dipinjam
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl py-20 text-center text-slate-500">
                        <i class="fa-solid fa-book-open text-4xl mb-4 opacity-35 text-indigo-400"></i>
                        <p class="text-lg font-medium">Novel tidak ditemukan</p>
                        <p class="text-sm mt-1 text-slate-600">Coba ganti filter kata kunci atau pilih genre yang lain.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="mt-20 border-t border-slate-900 bg-slate-950 py-8 text-center text-sm text-slate-600">
        <p>&copy; <?= date('Y'); ?> NoveLib. Dikembangkan untuk administrasi server UAS Cita Amelia.</p>
    </footer>

</body>
</html>

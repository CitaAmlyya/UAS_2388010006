<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Redirect jika tidak ada id di parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Get Novel Details
$query = "SELECT * FROM novels WHERE id = $id";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit;
}

$novel = mysqli_fetch_assoc($result);

// Generate HSL styled cover
$hue = abs(crc32($novel['judul'])) % 360; 
$bg_style = "background: linear-gradient(135deg, hsl($hue, 70%, 25%) 0%, hsl(($hue + 60) % 360, 60%, 10%) 100%)";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail: <?= htmlspecialchars($novel['judul']); ?> - NoveLib</title>
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
        <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <span class="text-xl font-bold tracking-wider bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">NoveLib</span>
            </a>
            <a href="index.php" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 rounded-lg text-sm font-semibold transition-all">
                <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </nav>

    <!-- Content Container -->
    <main class="max-w-5xl mx-auto px-6 py-12">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl grid grid-cols-1 md:grid-cols-3 gap-8 p-6 md:p-8">
            
            <!-- Left Side Cover -->
            <div class="md:col-span-1">
                <div style="<?= $bg_style; ?>" class="aspect-[3/4] w-full rounded-2xl flex flex-col justify-between p-6 text-white border border-white/5 shadow-2xl relative">
                    <span class="self-start text-[10px] px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-slate-200 border border-white/10 uppercase tracking-wider font-semibold">
                        <?= htmlspecialchars($novel['genre']); ?>
                    </span>
                    
                    <div class="space-y-2">
                        <h2 class="text-2xl font-extrabold tracking-wide drop-shadow-md leading-tight"><?= htmlspecialchars($novel['judul']); ?></h2>
                        <p class="text-sm text-slate-200/80 italic font-medium">Oleh: <?= htmlspecialchars($novel['penulis']); ?></p>
                    </div>
                </div>
                
                <div class="mt-6 space-y-3">
                    <?php if ($novel['status'] === 'tersedia') : ?>
                        <a href="pinjam.php?id=<?= $novel['id']; ?>" 
                            onclick="return confirm('Apakah Anda yakin ingin meminjam novel ini?')"
                            class="block w-full py-3.5 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/10 active:scale-[0.98] transition-all">
                            Pinjam Novel Ini
                        </a>
                    <?php else: ?>
                        <button disabled 
                            class="block w-full py-3.5 text-center bg-slate-850 text-slate-600 font-bold rounded-xl border border-slate-800 cursor-not-allowed">
                            Sedang Dipinjam
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side Details -->
            <div class="md:col-span-2 space-y-6 flex flex-col justify-between">
                <div>
                    <!-- Breadcrumbs / Genre Tag -->
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs font-semibold text-indigo-400 uppercase tracking-widest"><?= htmlspecialchars($novel['genre']); ?></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span>
                        <span class="text-xs text-slate-500 font-semibold">KODE: NVL-<?= str_pad($novel['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-100 mb-6 tracking-wide leading-tight">
                        <?= htmlspecialchars($novel['judul']); ?>
                    </h1>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 gap-4 p-4 bg-slate-950/50 border border-slate-850 rounded-2xl mb-6">
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Penulis</p>
                            <p class="text-sm font-semibold text-slate-300 mt-0.5"><?= htmlspecialchars($novel['penulis']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Penerbit</p>
                            <p class="text-sm font-semibold text-slate-300 mt-0.5"><?= htmlspecialchars($novel['penerbit']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Tahun Terbit</p>
                            <p class="text-sm font-semibold text-slate-300 mt-0.5"><?= htmlspecialchars($novel['tahun_terbit']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Status Ketersediaan</p>
                            <div class="mt-1">
                                <?php if ($novel['status'] === 'tersedia') : ?>
                                    <span class="text-xs px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md font-semibold">Tersedia</span>
                                <?php else: ?>
                                    <span class="text-xs px-2.5 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-md font-semibold font-semibold">Dipinjam</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Synopsis -->
                    <div class="space-y-3">
                        <h3 class="text-lg font-bold text-slate-200 border-b border-slate-800 pb-2">Sinopsis</h3>
                        <p class="text-slate-450 leading-relaxed text-sm text-justify">
                            <?= nl2br(htmlspecialchars($novel['sinopsis'])); ?>
                        </p>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-800/40 flex justify-between items-center text-xs text-slate-500">
                    <span class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> Ditambahkan: <?= date('d F Y', strtotime($novel['created_at'])); ?></span>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-20 border-t border-slate-900 bg-slate-950 py-8 text-center text-sm text-slate-600">
        <p>&copy; <?= date('Y'); ?> NoveLib. Dikembangkan untuk administrasi server UAS Cita Amelia.</p>
    </footer>

</body>
</html>

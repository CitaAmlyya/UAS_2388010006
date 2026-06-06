<?php
session_start();
require_once '../config/koneksi.php';

// Hanya admin yang boleh akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ---- Statistik Dashboard ----
$total_novels   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM novels"))['total'];
$total_users    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'anggota'"))['total'];
$total_pinjam   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'"))['total'];
$total_kembali  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dikembalikan'"))['total'];

// ---- Daftar peminjaman aktif ----
$pinjam_aktif = mysqli_query($koneksi,
    "SELECT p.id, u.username, n.judul, n.penulis, p.tanggal_pinjam
     FROM peminjaman p
     JOIN users u ON p.user_id = u.id
     JOIN novels n ON p.novel_id = n.id
     WHERE p.status = 'dipinjam'
     ORDER BY p.tanggal_pinjam DESC"
);

// ---- Handle kembalikan novel ----
if (isset($_GET['kembalikan']) && is_numeric($_GET['kembalikan'])) {
    $pinjam_id = intval($_GET['kembalikan']);

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($koneksi);
    try {
        // Ambil novel_id
        $stmt = mysqli_prepare($koneksi, "SELECT novel_id FROM peminjaman WHERE id = ? AND status = 'dipinjam'");
        mysqli_stmt_bind_param($stmt, "i", $pinjam_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if ($row = mysqli_fetch_assoc($res)) {
            $novel_id = $row['novel_id'];

            // Update status peminjaman
            $s1 = mysqli_prepare($koneksi, "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = CURDATE() WHERE id = ?");
            mysqli_stmt_bind_param($s1, "i", $pinjam_id);
            mysqli_stmt_execute($s1);
            mysqli_stmt_close($s1);

            // Update status novel kembali tersedia
            $s2 = mysqli_prepare($koneksi, "UPDATE novels SET status = 'tersedia' WHERE id = ?");
            mysqli_stmt_bind_param($s2, "i", $novel_id);
            mysqli_stmt_execute($s2);
            mysqli_stmt_close($s2);

            mysqli_commit($koneksi);
        }
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($koneksi);
    }
    header("Location: index.php?success=kembalikan");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NoveLib</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- Navbar -->
    <nav class="sticky top-0 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-wider bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">NoveLib</span>
                    <span class="ml-2 text-xs px-2 py-0.5 bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 rounded-md">Admin</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-400 hidden md:block">Halo, <strong class="text-white"><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="novels.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-sm font-semibold transition-all border border-slate-700">
                    <i class="fa-solid fa-book mr-2"></i>Kelola Novel
                </a>
                <a href="../logout.php" class="px-4 py-2 bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg text-sm font-semibold transition-all">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i>Keluar
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10 space-y-8">

        <?php if (isset($_GET['success'])) : ?>
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check"></i>
                <span>Novel berhasil dikembalikan.</span>
            </div>
        <?php endif; ?>

        <h1 class="text-3xl font-bold text-slate-100">Dashboard Admin</h1>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            $stats = [
                ['label' => 'Total Novel', 'value' => $total_novels,  'icon' => 'fa-book',          'color' => 'indigo'],
                ['label' => 'Total Anggota','value' => $total_users,  'icon' => 'fa-users',         'color' => 'purple'],
                ['label' => 'Sedang Dipinjam','value' => $total_pinjam, 'icon' => 'fa-book-open',   'color' => 'amber'],
                ['label' => 'Dikembalikan','value' => $total_kembali, 'icon' => 'fa-check-circle',  'color' => 'emerald'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-<?= $s['color'] ?>-600/10 flex items-center justify-center text-<?= $s['color'] ?>-400 text-xl border border-<?= $s['color'] ?>-500/20">
                    <i class="fa-solid <?= $s['icon'] ?>"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-100"><?= $s['value'] ?></p>
                    <p class="text-xs text-slate-500"><?= $s['label'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabel Peminjaman Aktif -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                <h2 class="font-bold text-slate-200 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-amber-400"></i> Peminjaman Aktif
                </h2>
                <a href="novels.php" class="text-xs text-indigo-400 hover:underline">Kelola Novel →</a>
            </div>

            <?php if (mysqli_num_rows($pinjam_aktif) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Anggota</th>
                            <th class="px-6 py-3 text-left">Judul Novel</th>
                            <th class="px-6 py-3 text-left">Penulis</th>
                            <th class="px-6 py-3 text-left">Tgl Pinjam</th>
                            <th class="px-6 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php while ($row = mysqli_fetch_assoc($pinjam_aktif)): ?>
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-200"><?= htmlspecialchars($row['username']) ?></td>
                            <td class="px-6 py-4 text-slate-300"><?= htmlspecialchars($row['judul']) ?></td>
                            <td class="px-6 py-4 text-slate-400 italic"><?= htmlspecialchars($row['penulis']) ?></td>
                            <td class="px-6 py-4 text-slate-400"><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                            <td class="px-6 py-4">
                                <a href="index.php?kembalikan=<?= $row['id'] ?>"
                                   onclick="return confirm('Tandai novel ini sudah dikembalikan?')"
                                   class="px-3 py-1.5 bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white rounded-lg text-xs font-semibold transition-all border border-emerald-500/20">
                                    Kembalikan
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="py-16 text-center text-slate-500">
                <i class="fa-solid fa-inbox text-3xl mb-3 block opacity-40 text-indigo-400"></i>
                Tidak ada peminjaman aktif saat ini.
            </div>
            <?php endif; ?>
        </div>

    </main>

    <footer class="mt-20 border-t border-slate-900 bg-slate-950 py-8 text-center text-sm text-slate-600">
        <p>&copy; <?= date('Y') ?> NoveLib &mdash; Admin Panel</p>
    </footer>

</body>
</html>

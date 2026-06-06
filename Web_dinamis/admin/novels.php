<?php
session_start();
require_once '../config/koneksi.php';

// Hanya admin yang boleh akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$success = "";
$error   = "";

// ---- HAPUS NOVEL ----
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = mysqli_prepare($koneksi, "DELETE FROM novels WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: novels.php?success=hapus");
    exit;
}

// ---- TAMBAH / EDIT NOVEL ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul        = trim($_POST['judul']);
    $penulis      = trim($_POST['penulis']);
    $penerbit     = trim($_POST['penerbit']);
    $tahun_terbit = intval($_POST['tahun_terbit']);
    $genre        = trim($_POST['genre']);
    $sinopsis     = trim($_POST['sinopsis']);
    $edit_id      = isset($_POST['edit_id']) && is_numeric($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

    if (empty($judul) || empty($penulis) || empty($penerbit) || empty($genre) || empty($sinopsis) || $tahun_terbit < 1000) {
        $error = "Semua field wajib diisi dengan benar!";
    } else {
        if ($edit_id > 0) {
            $stmt = mysqli_prepare($koneksi, "UPDATE novels SET judul=?, penulis=?, penerbit=?, tahun_terbit=?, genre=?, sinopsis=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssissi", $judul, $penulis, $penerbit, $tahun_terbit, $genre, $sinopsis, $edit_id);
            $action = "edit";
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO novels (judul, penulis, penerbit, tahun_terbit, genre, sinopsis) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sssiss", $judul, $penulis, $penerbit, $tahun_terbit, $genre, $sinopsis);
            $action = "tambah";
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: novels.php?success=$action");
        exit;
    }
}

// ---- Ambil data edit (jika ada) ----
$edit_novel = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM novels WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $edit_novel = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}

// ---- Ambil semua novel ----
$novels = mysqli_query($koneksi, "SELECT * FROM novels ORDER BY id DESC");

// ---- Pesan sukses ----
$sukses_msg = [
    'tambah'     => 'Novel berhasil ditambahkan.',
    'edit'       => 'Novel berhasil diperbarui.',
    'hapus'      => 'Novel berhasil dihapus.',
    'kembalikan' => 'Novel berhasil dikembalikan.',
];
if (isset($_GET['success']) && array_key_exists($_GET['success'], $sukses_msg)) {
    $success = $sukses_msg[$_GET['success']];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Novel - NoveLib Admin</title>
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
                <a href="index.php" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <span class="text-xl font-bold tracking-wider bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">NoveLib</span>
                </a>
                <span class="text-xs px-2 py-0.5 bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 rounded-md">Admin</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-sm font-semibold transition-all border border-slate-700">
                    <i class="fa-solid fa-gauge mr-2"></i>Dashboard
                </a>
                <a href="../logout.php" class="px-4 py-2 bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg text-sm font-semibold transition-all">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i>Keluar
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10 space-y-8">

        <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i><span><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i><span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Form Tambah / Edit -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sticky top-24">
                    <h2 class="font-bold text-slate-200 text-lg mb-5 flex items-center gap-2">
                        <i class="fa-solid <?= $edit_novel ? 'fa-pen' : 'fa-plus' ?> text-indigo-400"></i>
                        <?= $edit_novel ? 'Edit Novel' : 'Tambah Novel' ?>
                    </h2>
                    <form action="novels.php" method="POST" class="space-y-4">
                        <?php if ($edit_novel): ?>
                            <input type="hidden" name="edit_id" value="<?= $edit_novel['id'] ?>">
                        <?php endif; ?>

                        <?php
                        $fields = [
                            ['name' => 'judul',        'label' => 'Judul Novel',   'type' => 'text',   'ph' => 'Judul novel'],
                            ['name' => 'penulis',      'label' => 'Penulis',       'type' => 'text',   'ph' => 'Nama penulis'],
                            ['name' => 'penerbit',     'label' => 'Penerbit',      'type' => 'text',   'ph' => 'Nama penerbit'],
                            ['name' => 'tahun_terbit', 'label' => 'Tahun Terbit',  'type' => 'number', 'ph' => '2024'],
                            ['name' => 'genre',        'label' => 'Genre',         'type' => 'text',   'ph' => 'Fiksi Sejarah, dll'],
                        ];
                        foreach ($fields as $f):
                            $val = $edit_novel ? htmlspecialchars($edit_novel[$f['name']]) : '';
                        ?>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1"><?= $f['label'] ?></label>
                            <input type="<?= $f['type'] ?>" name="<?= $f['name'] ?>" value="<?= $val ?>" required
                                placeholder="<?= $f['ph'] ?>"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <?php endforeach; ?>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Sinopsis</label>
                            <textarea name="sinopsis" rows="4" required placeholder="Deskripsi singkat novel..."
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"><?= $edit_novel ? htmlspecialchars($edit_novel['sinopsis']) : '' ?></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition-all">
                                <?= $edit_novel ? 'Simpan Perubahan' : 'Tambah Novel' ?>
                            </button>
                            <?php if ($edit_novel): ?>
                            <a href="novels.php" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-400 rounded-xl text-sm font-semibold transition-all border border-slate-700">
                                Batal
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Novel -->
            <div class="lg:col-span-2">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="font-bold text-slate-200 text-lg flex items-center gap-2">
                            <i class="fa-solid fa-list text-indigo-400"></i> Daftar Novel
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Judul</th>
                                    <th class="px-4 py-3 text-left">Penulis</th>
                                    <th class="px-4 py-3 text-left">Genre</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php while ($novel = mysqli_fetch_assoc($novels)): ?>
                                <tr class="hover:bg-slate-800/40 transition-colors <?= ($edit_novel && $edit_novel['id'] == $novel['id']) ? 'bg-indigo-950/30' : '' ?>">
                                    <td class="px-4 py-3 font-medium text-slate-200 max-w-[180px] truncate"><?= htmlspecialchars($novel['judul']) ?></td>
                                    <td class="px-4 py-3 text-slate-400 italic"><?= htmlspecialchars($novel['penulis']) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs px-2 py-0.5 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-md"><?= htmlspecialchars($novel['genre']) ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($novel['status'] === 'tersedia'): ?>
                                            <span class="text-xs px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md">Tersedia</span>
                                        <?php else: ?>
                                            <span class="text-xs px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-md">Dipinjam</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1.5">
                                            <a href="novels.php?edit=<?= $novel['id'] ?>"
                                               class="px-2.5 py-1 bg-indigo-600/10 hover:bg-indigo-600 text-indigo-400 hover:text-white rounded-lg text-xs font-semibold transition-all border border-indigo-500/20">
                                                Edit
                                            </a>
                                            <a href="novels.php?hapus=<?= $novel['id'] ?>"
                                               onclick="return confirm('Yakin hapus novel ini?')"
                                               class="px-2.5 py-1 bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg text-xs font-semibold transition-all border border-rose-500/20">
                                                Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="mt-20 border-t border-slate-900 bg-slate-950 py-8 text-center text-sm text-slate-600">
        <p>&copy; <?= date('Y') ?> NoveLib &mdash; Admin Panel</p>
    </footer>
</body>
</html>

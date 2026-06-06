<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek jika yang login adalah admin
if ($_SESSION['role'] == 'admin') {
    header("Location: admin/index.php");
    exit;
}

// Redirect jika tidak ada id di parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$novel_id = intval($_GET['id']);
$user_id  = $_SESSION['user_id'];

// FIX: Aktifkan error reporting mysqli agar exception bisa di-catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Cek ketersediaan novel menggunakan prepared statement
$stmt = mysqli_prepare($koneksi, "SELECT status FROM novels WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if (mysqli_num_rows($check_result) === 1) {
    $novel = mysqli_fetch_assoc($check_result);

    if ($novel['status'] === 'tersedia') {
        // Mulai transaksi database untuk menjaga integritas data
        mysqli_begin_transaction($koneksi);

        try {
            // 1. Insert ke tabel peminjaman
            $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO peminjaman (user_id, novel_id, tanggal_pinjam, status) VALUES (?, ?, CURDATE(), 'dipinjam')");
            mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $novel_id);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);

            // 2. Update status novel menjadi dipinjam
            $stmt_update = mysqli_prepare($koneksi, "UPDATE novels SET status = 'dipinjam' WHERE id = ?");
            mysqli_stmt_bind_param($stmt_update, "i", $novel_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);

            // Commit transaksi jika semuanya sukses
            mysqli_commit($koneksi);

            echo "<script>
                alert('Novel berhasil dipinjam!');
                window.location.href = 'index.php';
            </script>";
            exit;
        } catch (mysqli_sql_exception $e) {
            // Rollback jika ada error
            mysqli_rollback($koneksi);
            echo "<script>
                alert('Gagal meminjam novel. Terjadi kesalahan sistem.');
                window.location.href = 'index.php';
            </script>";
            exit;
        }
    } else {
        echo "<script>
            alert('Novel ini sedang dipinjam oleh orang lain.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>

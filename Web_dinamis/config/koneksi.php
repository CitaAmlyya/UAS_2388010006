<?php
$host     = getenv('DB_HOST')     ?: 'db';
$user     = getenv('DB_USER')     ?: 'novelib_user';
$password = getenv('DB_PASSWORD') ?: 'novelib_pass123';
$database = getenv('DB_NAME')     ?: 'db_perpustakaan_novel';

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");
?>

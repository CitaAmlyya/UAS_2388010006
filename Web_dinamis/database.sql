-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS db_perpustakaan;
USE db_perpustakaan;

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'anggota') DEFAULT 'anggota',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Novels
CREATE TABLE IF NOT EXISTS novels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit INT NOT NULL,
    genre VARCHAR(50) NOT NULL,
    sinopsis TEXT NOT NULL,
    cover_image VARCHAR(255) DEFAULT 'default_cover.jpg',
    status ENUM('tersedia', 'dipinjam') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    novel_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE DEFAULT NULL,
    status ENUM('dipinjam', 'dikembalikan') DEFAULT 'dipinjam',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Users
-- Password admin123 -> $2y$10$zd6bkWOfkES.EXUEeROxBuMbJy4dLbTeHVtNVuuERW9SoYl/36d0y
-- Password user123  -> $2y$10$EHG6w4RmzPOaNRBrwqbvT.Ypa5LABnEpI5DHI3QdDuD9rLiIAf.a2
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$zd6bkWOfkES.EXUEeROxBuMbJy4dLbTeHVtNVuuERW9SoYl/36d0y', 'admin@novelib.com', 'admin'),
('cita', '$2y$10$EHG6w4RmzPOaNRBrwqbvT.Ypa5LABnEpI5DHI3QdDuD9rLiIAf.a2', 'cita@gmail.com', 'anggota');

-- Insert Default Novels
INSERT INTO novels (judul, penulis, penerbit, tahun_terbit, genre, sinopsis, cover_image, status) VALUES 
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 'Fiksi Sejarah', 'Kisah perjuangan Minke, seorang pemuda pribumi di era kolonial Belanda, yang menghadapi diskriminasi, cinta yang tragis dengan Annelies, dan pergolakan sosial-politik pada masa kebangkitan nasional.', 'bumi_manusia.jpg', 'tersedia'),
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'Fiksi Inspiratif', 'Perjalanan sepuluh anak di Pulau Belitung yang berjuang menempuh pendidikan di sekolah Muhammadiyah yang sangat sederhana namun dipenuhi mimpi dan dedikasi guru luar biasa.', 'laskar_pelangi.jpg', 'tersedia'),
('Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'Gramedia Pustaka Utama', 1982, 'Fiksi Sosial', 'Mengisahkan kehidupan Srintil, seorang penari ronggeng di desa terpencil Dukuh Paruk, yang terperangkap dalam adat istiadat tradisi serta pergolakan politik tahun 1965.', 'ronggeng_dukuh_paruk.jpg', 'tersedia'),
('Hujan', 'Tere Liye', 'Gramedia Pustaka Utama', 2016, 'Sains Fiksi', 'Mengambil latar masa depan tentang persahabatan, cinta, dan perpisahan di tengah dunia yang dilanda bencana alam dahsyat dan kemajuan teknologi yang mengancam eksistensi manusia.', 'hujan.jpg', 'tersedia'),
('Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 2009, 'Fiksi Pendidikan', 'Kisah Alif dan kelima sahabatnya di Pondok Madani Jawa Timur yang memegang teguh pepatah "Man Jadda Wajada" dalam menggapai cita-cita mereka hingga ke ujung dunia.', 'negeri_5_menara.jpg', 'tersedia');

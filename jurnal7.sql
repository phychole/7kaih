-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 24 Feb 2026 pada 14.59
-- Versi server: 8.4.3
-- Versi PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jurnal7`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_kegiatan`
--

CREATE TABLE `detail_kegiatan` (
  `id` int NOT NULL,
  `id_kegiatan` int NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `agama` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru_wali_siswa`
--

CREATE TABLE `guru_wali_siswa` (
  `id` int NOT NULL,
  `id_guru` int NOT NULL,
  `id_siswa` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_belajar`
--

CREATE TABLE `jenis_belajar` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jenis_belajar`
--

INSERT INTO `jenis_belajar` (`id`, `nama`) VALUES
(1, 'Mengerjakan tugas sekolah'),
(2, 'Membaca buku pelajaran'),
(3, 'Membaca buku cerita'),
(4, 'Les privat'),
(5, 'Belajar kelompok'),
(6, 'Mengerjakan PR'),
(7, 'Mengulang materi pelajaran'),
(8, 'Membuat rangkuman'),
(9, 'Membuat catatan ringkas'),
(10, 'Belajar melalui video edukasi'),
(11, 'Belajar melalui aplikasi belajar'),
(12, 'Menghafal materi'),
(13, 'Mengerjakan latihan soal'),
(14, 'Try out online'),
(15, 'Belajar mandiri'),
(16, 'Belajar bersama orang tua'),
(17, 'Konsultasi materi dengan tutor'),
(18, 'Membaca artikel edukasi'),
(19, 'Menonton dokumenter pembelajaran'),
(20, 'Eksperimen sains sederhana'),
(21, 'Praktik keterampilan (misal kerajinan tangan)'),
(22, 'Mengerjakan worksheet'),
(23, 'Belajar bahasa asing'),
(24, 'Mendengarkan podcast edukasi'),
(25, 'Mencari informasi tugas di internet'),
(26, 'Membuat proyek rumah (project based learning)'),
(27, 'Latihan menulis'),
(28, 'Latihan berhitung'),
(29, 'Latihan presentasi'),
(30, 'Diskusi materi dengan teman'),
(31, 'Belajar menggunakan modul sekolah'),
(32, 'Mengikuti bimbingan belajar (bimbel)'),
(33, 'Belajar lewat permainan edukasi'),
(34, 'Latihan soal ujian'),
(35, 'Latihan membaca cepat'),
(36, 'Belajar hafalan Al-Qur\'an/kitab (bagi yang menjalankan)'),
(37, 'Belajar keterampilan rumah (life skills)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_kegiatan`
--

CREATE TABLE `jenis_kegiatan` (
  `id` int NOT NULL,
  `nama_kegiatan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jenis_kegiatan`
--

INSERT INTO `jenis_kegiatan` (`id`, `nama_kegiatan`) VALUES
(1, 'Bangun Pagi'),
(2, 'Beribadah'),
(3, 'Berolahraga'),
(4, 'Makan Sehat dan Bergizi'),
(5, 'Gemar Belajar'),
(6, 'Bermasyarakat'),
(7, 'Tidur Cepat');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_makanan`
--

CREATE TABLE `jenis_makanan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jenis_makanan`
--

INSERT INTO `jenis_makanan` (`id`, `nama`) VALUES
(2, 'Nasi merah'),
(3, 'Nasi putih'),
(4, 'Ubi jalar'),
(5, 'Kentang'),
(6, 'Singkong'),
(7, 'Jagung'),
(8, 'Oatmeal'),
(9, 'Roti gandum'),
(10, 'Pasta gandum'),
(11, 'Quinoa'),
(12, 'Sereal gandum utuh'),
(13, 'Makaroni gandum'),
(14, 'Bihun beras'),
(15, 'Mie telur'),
(16, 'Telur'),
(17, 'Tahu'),
(18, 'Tempe'),
(19, 'Ikan'),
(20, 'Ayam tanpa lemak'),
(21, 'Daging sapi tanpa lemak'),
(22, 'Kacang merah'),
(23, 'Kacang hijau'),
(24, 'Edamame'),
(25, 'Ikan salmon'),
(26, 'Ikan tuna'),
(27, 'Bayam'),
(28, 'Brokoli'),
(29, 'Wortel'),
(30, 'Kol'),
(31, 'Selada'),
(32, 'Timun'),
(33, 'Tomat'),
(34, 'Kangkung'),
(35, 'Buncis'),
(36, 'Paprika'),
(37, 'Kacang panjang'),
(38, 'Apel'),
(39, 'Pisang'),
(40, 'Jeruk'),
(41, 'Pepaya'),
(42, 'Semangka'),
(43, 'Melon'),
(44, 'Anggur'),
(45, 'Pear'),
(46, 'Mangga'),
(47, 'Alpukat'),
(48, 'Stroberi'),
(49, 'Air putih'),
(50, 'Susu'),
(51, 'Teh tawar'),
(52, 'Teh hijau'),
(53, 'Jus buah tanpa gula'),
(54, 'Susu kedelai'),
(55, 'Infused water'),
(56, 'Air kelapa'),
(57, 'Smoothies buah'),
(58, 'Susu almond'),
(59, 'Teh manis'),
(60, 'Susu coklat'),
(61, 'Jus buah dengan sedikit gula');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_masyarakat`
--

CREATE TABLE `jenis_masyarakat` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jenis_masyarakat`
--

INSERT INTO `jenis_masyarakat` (`id`, `nama`) VALUES
(1, 'Karangtaruna'),
(2, 'Siskamling'),
(3, 'Kerja bakti'),
(4, 'Karang taruna'),
(5, 'Gotong royong'),
(6, 'Posyandu remaja'),
(7, 'Bakti sosial'),
(8, 'Donor darah (usia memenuhi syarat)'),
(9, 'Penggalangan dana sosial'),
(10, 'Membersihkan lingkungan'),
(11, 'Penghijauan / penanaman pohon'),
(12, 'Pengelolaan bank sampah'),
(13, 'Penyuluhan kesehatan'),
(14, 'Pengumpulan pakaian layak pakai'),
(15, 'Kegiatan 17 Agustusan'),
(16, 'Lomba kebersihan lingkungan'),
(17, 'Senam bersama warga'),
(18, 'Piket kebersihan lingkungan RT/RW'),
(19, 'Menjadi panitia acara desa/kelurahan'),
(20, 'Menjadi relawan bencana'),
(21, 'Menjadi relawan perpustakaan desa'),
(22, 'Bazar sosial'),
(23, 'Pengajaran gratis untuk anak-anak'),
(24, 'Pendampingan belajar adik-adik SD'),
(25, 'Remaja masjid'),
(26, 'Kegiatan pemuda gereja'),
(27, 'Kegiatan pemuda wihara'),
(28, 'Kegiatan pemuda pura'),
(29, 'Pengumpulan sampah plastik untuk daur ulang'),
(30, 'Menjadi MC acara masyarakat'),
(31, 'Lomba seni budaya tingkat desa'),
(32, 'Pawai atau karnaval desa'),
(33, 'Kegiatan keamanan kampung'),
(34, 'Pembuatan mural lingkungan'),
(35, 'Menjaga pos ronda'),
(36, 'Kegiatan musik pemuda'),
(37, 'Kegiatan olahraga antar-RT/RW'),
(38, 'Kegiatan bersih sungai'),
(39, 'Kegiatan bersih pantai (untuk wilayah pesisir)'),
(40, 'Kegiatan UMKM pemuda');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_olahraga`
--

CREATE TABLE `jenis_olahraga` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jenis_olahraga`
--

INSERT INTO `jenis_olahraga` (`id`, `nama`) VALUES
(3, 'Lari'),
(4, 'Jogging'),
(5, 'Renang'),
(6, 'Bersepeda'),
(7, 'Sepak bola'),
(8, 'Bola basket'),
(9, 'Bulu tangkis'),
(10, 'Tenis'),
(11, 'Tenis meja'),
(12, 'Voli'),
(13, 'Futsal'),
(14, 'Senam'),
(15, 'Taekwondo'),
(16, 'Karate'),
(17, 'Silat');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_siswa`
--

CREATE TABLE `jurnal_siswa` (
  `id` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_kegiatan` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_bangun_pagi` time DEFAULT NULL,
  `catatan` text,
  `nilai` enum('Sudah','Belum') DEFAULT 'Sudah',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `jam_bangun` time DEFAULT NULL,
  `jam_tidur` time DEFAULT NULL,
  `ibadah` varchar(100) DEFAULT NULL,
  `jam_ibadah` time DEFAULT NULL,
  `olahraga` varchar(100) DEFAULT NULL,
  `belajar` varchar(100) DEFAULT NULL,
  `waktu_makan` enum('Pagi','Siang','Malam') DEFAULT NULL,
  `makanan_sehat` text,
  `masyarakat` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `id_guru_wali` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `id_guru_wali`) VALUES
(1, '1A', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `agama` varchar(50) NOT NULL,
  `id_kelas` int NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL DEFAULT 'default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nisn`, `nama`, `jenis_kelamin`, `agama`, `id_kelas`, `password`, `foto`, `created_at`) VALUES
(6, '1234567890', 'ANAK BARU', 'L', 'Islam', 1, '$2y$10$qjym.QWZ6XNfmbstdPsn0uXVYcMplsM/m2HtUri86p.mztzmvqM0O', 'default.png', '2025-12-17 18:16:51'),
(7, '1234567891', 'ANAK KEDUA', 'L', 'Katolik', 1, '$2y$10$qjym.QWZ6XNfmbstdPsn0uXVYcMplsM/m2HtUri86p.mztzmvqM0O', 'default.png', '2025-12-17 18:18:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kurikulum') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@example.com', '$2y$10$qjym.QWZ6XNfmbstdPsn0uXVYcMplsM/m2HtUri86p.mztzmvqM0O', 'admin', '2025-11-20 02:38:51'),
(2, 'Kurikulum', 'kurikulum@example.com', '$2y$10$qjym.QWZ6XNfmbstdPsn0uXVYcMplsM/m2HtUri86p.mztzmvqM0O', 'kurikulum', '2025-11-20 02:38:51');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_kegiatan`
--
ALTER TABLE `detail_kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kegiatan` (`id_kegiatan`);

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `guru_wali_siswa`
--
ALTER TABLE `guru_wali_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indeks untuk tabel `jenis_belajar`
--
ALTER TABLE `jenis_belajar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jenis_kegiatan`
--
ALTER TABLE `jenis_kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jenis_makanan`
--
ALTER TABLE `jenis_makanan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jenis_masyarakat`
--
ALTER TABLE `jenis_masyarakat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jenis_olahraga`
--
ALTER TABLE `jenis_olahraga`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jurnal_siswa`
--
ALTER TABLE `jurnal_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_jurnal_harian` (`id_siswa`,`tanggal`,`id_kegiatan`),
  ADD UNIQUE KEY `uniq_ibadah` (`id_siswa`,`tanggal`,`id_kegiatan`,`ibadah`),
  ADD UNIQUE KEY `uniq_makan` (`id_siswa`,`tanggal`,`id_kegiatan`,`waktu_makan`),
  ADD KEY `id_kegiatan` (`id_kegiatan`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nisn` (`nisn`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_kegiatan`
--
ALTER TABLE `detail_kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `guru_wali_siswa`
--
ALTER TABLE `guru_wali_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `jenis_belajar`
--
ALTER TABLE `jenis_belajar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `jenis_kegiatan`
--
ALTER TABLE `jenis_kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `jenis_makanan`
--
ALTER TABLE `jenis_makanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `jenis_masyarakat`
--
ALTER TABLE `jenis_masyarakat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `jenis_olahraga`
--
ALTER TABLE `jenis_olahraga`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `jurnal_siswa`
--
ALTER TABLE `jurnal_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_kegiatan`
--
ALTER TABLE `detail_kegiatan`
  ADD CONSTRAINT `detail_kegiatan_ibfk_1` FOREIGN KEY (`id_kegiatan`) REFERENCES `jenis_kegiatan` (`id`);

--
-- Ketidakleluasaan untuk tabel `guru_wali_siswa`
--
ALTER TABLE `guru_wali_siswa`
  ADD CONSTRAINT `guru_wali_siswa_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guru_wali_siswa_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jurnal_siswa`
--
ALTER TABLE `jurnal_siswa`
  ADD CONSTRAINT `jurnal_siswa_ibfk_2` FOREIGN KEY (`id_kegiatan`) REFERENCES `jenis_kegiatan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

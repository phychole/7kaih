<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_siswa = $_SESSION['user_id'];
$id       = (int)($_GET['id'] ?? 0);

// ID tidak valid
if ($id <= 0) {
    header("Location: riwayat.php?msg=invalid");
    exit;
}

// Cek apakah jurnal benar milik siswa ini
$cek = $conn->prepare("SELECT id FROM jurnal_siswa WHERE id=? AND id_siswa=? LIMIT 1");
$cek->bind_param("ii", $id, $id_siswa);
$cek->execute();
$hasil = $cek->get_result()->num_rows;

if ($hasil == 0) {
    // Tidak berhak menghapus / jurnal tidak ada
    header("Location: riwayat.php?msg=notfound");
    exit;
}

// Hapus data
$hapus = $conn->prepare("DELETE FROM jurnal_siswa WHERE id=? AND id_siswa=? LIMIT 1");
$hapus->bind_param("ii", $id, $id_siswa);
$hapus->execute();

header("Location: riwayat.php?msg=deleted");
exit;

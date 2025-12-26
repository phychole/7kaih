<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Cek apakah kelas masih dipakai siswa
$cek = $conn->prepare("SELECT COUNT(*) AS total FROM siswa WHERE id_kelas=?");
$cek->bind_param("i", $id);
$cek->execute();
$total = $cek->get_result()->fetch_assoc()['total'];

if ($total > 0) {
    // Tidak boleh dihapus
    header("Location: kelas.php?error=msh_ada_siswa&jumlah=$total");
    exit;
}

// 2. Jika aman → hapus kelas
$hapus = $conn->prepare("DELETE FROM kelas WHERE id=?");
$hapus->bind_param("i", $id);
$hapus->execute();

header("Location: kelas.php?msg=deleted");
exit;

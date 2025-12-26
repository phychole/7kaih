<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: siswa.php");
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    header("Location: siswa.php?error=csrf");
    exit;
}

$conn->begin_transaction();

try {
    // Ambil daftar foto dulu (buat hapus file, kecuali default.png)
    $res = $conn->query("SELECT foto FROM siswa");
    $fotos = [];
    while ($r = $res->fetch_assoc()) {
        if (!empty($r['foto']) && $r['foto'] !== 'default.png') {
            $fotos[] = $r['foto'];
        }
    }

    // Hapus semua siswa
    // NOTE: kalau ada FK dari tabel lain (misal jurnal_siswa) dan tidak cascade,
    // query ini bisa gagal. Pastikan FK-nya ON DELETE CASCADE atau hapus jurnal dulu.
    $conn->query("DELETE FROM siswa");

    // Hapus file foto
    foreach ($fotos as $f) {
        $path = "../uploads/siswa/" . $f;
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    $conn->commit();
    header("Location: siswa.php?msg=deleted_all");
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    header("Location: siswa.php?error=delete_all_failed");
    exit;
}

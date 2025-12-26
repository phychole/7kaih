<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Hapus semua guru
$conn->query("DELETE FROM guru");

// Reset auto increment (opsional)
$conn->query("ALTER TABLE guru AUTO_INCREMENT = 1");

// Redirect dengan notifikasi
header("Location: guru.php?msg=hapus_semua");
exit;

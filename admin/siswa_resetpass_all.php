<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Wajib POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: siswa.php?error=csrf");
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    header("Location: siswa.php?error=csrf");
    exit;
}

try {
    // Ambil semua siswa (id, nisn)
    $res = $conn->query("SELECT id, nisn FROM siswa");
    if (!$res) {
        throw new Exception("Query gagal.");
    }

    // Siapkan statement update (password di-set ke hash dari NISN)
    $stmt = $conn->prepare("UPDATE siswa SET password=? WHERE id=?");
    if (!$stmt) {
        throw new Exception("Prepare gagal.");
    }

    $updated = 0;

    while ($row = $res->fetch_assoc()) {
        $id  = (int)$row['id'];
        $nisn = (string)($row['nisn'] ?? '');

        // Jika NISN kosong, skip saja (biar aman)
        if (trim($nisn) === '') continue;

        $hash = password_hash($nisn, PASSWORD_DEFAULT);

        $stmt->bind_param("si", $hash, $id);
        $stmt->execute();

        // Jika benar-benar berubah / sukses update
        if ($stmt->affected_rows >= 0) {
            $updated++;
        }
    }

    $stmt->close();

    // Notif sukses
    header("Location: siswa.php?msg=resetpass_all&count=" . $updated);
    exit;

} catch (Throwable $e) {
    // Jika mau debug: error_log($e->getMessage());
    header("Location: siswa.php?error=resetpass_all_failed");
    exit;
}

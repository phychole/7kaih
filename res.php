<?php
// ===========================================
//  SECRET PASSWORD RESET TOOL
//  Hapus file ini setelah digunakan!
// ===========================================

require 'config.php';

// Password baru yang ingin digunakan
$newPasswordPlain = "123456789"; // ganti sesuai keinginan
$newPasswordHashed = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

echo "<h2>Reset Password Semua User</h2>";
echo "<p>Password baru: <strong>$newPasswordPlain</strong></p>";

// ===============================
// RESET ADMIN & KURIKULUM
// ===============================
$reset1 = $conn->query("UPDATE users SET password='$newPasswordHashed'");
if ($reset1) {
    echo "<p>✔ Password ADMIN & KURIKULUM berhasil direset.</p>";
} else {
    echo "<p>❌ Gagal reset admin/kurikulum: " . $conn->error . "</p>";
}

// ===============================
// RESET GURU
// ===============================
$reset2 = $conn->query("UPDATE guru SET password='$newPasswordHashed'");
if ($reset2) {
    echo "<p>✔ Password GURU berhasil direset.</p>";
} else {
    echo "<p>❌ Gagal reset guru: " . $conn->error . "</p>";
}

// ===============================
// RESET SISWA
// ===============================
$reset3 = $conn->query("UPDATE siswa SET password='$newPasswordHashed'");
if ($reset3) {
    echo "<p>✔ Password SISWA berhasil direset.</p>";
} else {
    echo "<p>❌ Gagal reset siswa: " . $conn->error . "</p>";
}

echo "<hr><p><strong>Selesai.</strong></p>";
echo "<p><b>PERINGATAN:</b> Hapus file ini sekarang juga!</p>";

?>

<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

if (!isset($_FILES['file_csv'])) {
    die("File tidak ditemukan.");
}

$file = $_FILES['file_csv']['tmp_name'];

if (!file_exists($file)) {
    die("File tidak valid.");
}

$csv = fopen($file, "r");
$first = true;

$inserted = 0;
$skipped  = 0;

while (($row = fgetcsv($csv, 1000, ";")) !== FALSE) {

    // Lewati baris judul
    if ($first) {
        $first = false;
        continue;
    }

    // Pastikan format sesuai
    if (count($row) < 5) {
        $skipped++;
        continue;
    }

    list($nama, $email, $jk, $agama, $password) = $row;

    // Cek email duplikat
    $cek = $conn->query("SELECT id FROM guru WHERE email='$email'");
    if ($cek->num_rows > 0) {
        $skipped++;
        continue;
    }

    // Hash password
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert
    $stmt = $conn->prepare("
        INSERT INTO guru (nama, email, jenis_kelamin, agama, password)
        VALUES (?,?,?,?,?)
    ");
    $stmt->bind_param("sssss", $nama, $email, $jk, $agama, $pass_hash);
    $stmt->execute();

    $inserted++;
}

fclose($csv);

// Redirect
header("Location: guru.php?msg=import&add=$inserted&skip=$skipped");
exit;

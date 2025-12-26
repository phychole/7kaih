<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$file = $_FILES['file_csv']['tmp_name'];

if (!file_exists($file)) {
    die("File tidak ditemukan.");
}

$csv = fopen($file, "r");
$first = true;
$inserted = 0;
$skipped  = 0;

while (($row = fgetcsv($csv, 1000, ",")) !== FALSE) {

    if ($first) { $first = false; continue; }
    if (count($row) < 1) { $skipped++; continue; }

    $nama = trim($row[0]);
    if ($nama == "") { $skipped++; continue; }

    // cek duplikat
    $cek = $conn->prepare("SELECT id FROM jenis_belajar WHERE nama=?");
    $cek->bind_param("s", $nama);
    $cek->execute();

    if ($cek->get_result()->num_rows > 0) {
        $skipped++;
        continue;
    }

    // insert
    $stmt = $conn->prepare("INSERT INTO jenis_belajar (nama) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $stmt->execute();

    $inserted++;
}

fclose($csv);

header("Location: belajar.php?msg=import&add=$inserted&skip=$skipped");
exit;

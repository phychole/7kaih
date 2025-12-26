<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

// cek file
if (!isset($_FILES['file_csv'])) {
    die("File tidak ditemukan.");
}

$file = $_FILES['file_csv']['tmp_name'];

if (!file_exists($file)) {
    die("File CSV tidak valid.");
}

$csv = fopen($file, "r");
$first = true;
$inserted = 0;
$skipped  = 0;

while (($row = fgetcsv($csv, 1000, ",")) !== FALSE) {

    if ($first) { 
        $first = false; 
        continue; 
    }

    if (count($row) < 1) { 
        $skipped++; 
        continue; 
    }

    $nama = trim($row[0]);

    if ($nama == "") {
        $skipped++;
        continue;
    }

    // cek duplikat
    $cek = $conn->prepare("SELECT id FROM jenis_olahraga WHERE nama=?");
    $cek->bind_param("s", $nama);
    $cek->execute();
    $cekRes = $cek->get_result();

    if ($cekRes->num_rows > 0) {
        $skipped++;
        continue;
    }

    // insert
    $stmt = $conn->prepare("INSERT INTO jenis_olahraga (nama) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $stmt->execute();

    $inserted++;
}

fclose($csv);

header("Location: olahraga.php?msg=import&add=$inserted&skip=$skipped");
exit;

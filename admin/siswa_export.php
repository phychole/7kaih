<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$kelas = isset($_GET['kelas']) ? intval($_GET['kelas']) : 0;
$cari  = isset($_GET['cari'])  ? $_GET['cari'] : '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="siswa_export.csv"');

$output = fopen("php://output", "w");

// Header CSV
fputcsv($output, ["NISN","Nama","Jenis Kelamin","Agama","Kelas"]);

// Where
$where = "WHERE 1=1";

if ($kelas > 0) {
    $where .= " AND s.id_kelas=$kelas";
}

if ($cari != "") {
    $safe = $conn->real_escape_string($cari);
    $where .= " AND (s.nama LIKE '%$safe%' OR s.nisn LIKE '%$safe%' OR s.agama LIKE '%$safe%')";
}

$q = $conn->query("
    SELECT s.*, k.nama_kelas 
    FROM siswa s
    JOIN kelas k ON s.id_kelas=k.id
    $where
    ORDER BY s.nama ASC
");

while ($d = $q->fetch_assoc()) {
    fputcsv($output, [
        $d['nisn'],
        $d['nama'],
        $d['jenis_kelamin'],
        $d['agama'],
        $d['nama_kelas']
    ]);
}

fclose($output);
exit;

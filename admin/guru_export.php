<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_guru.csv');

$output = fopen("php://output", "w");

// Judul kolom
fputcsv($output, ["Nama", "Email", "Jenis Kelamin", "Agama"]);

// Ambil data
$data = $conn->query("SELECT nama, email, jenis_kelamin, agama FROM guru ORDER BY nama ASC");

while ($row = $data->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;

<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') exit;

$q = $_GET['q'] ?? '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// cari siswa yang BELUM punya guru wali
$stmt = $conn->prepare("
    SELECT s.id, s.nama, s.nisn, k.nama_kelas
    FROM siswa s
    JOIN kelas k ON s.id_kelas = k.id
    LEFT JOIN guru_wali_siswa gw ON gw.id_siswa = s.id
    WHERE gw.id IS NULL
    AND (s.nama LIKE ? OR s.nisn LIKE ?)
    ORDER BY s.nama ASC
    LIMIT 20
");

$search = "%".$q."%";
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row['id'],
        "nama" => $row['nama'],
        "nisn" => $row['nisn'],
        "kelas" => $row['nama_kelas']
    ];
}

echo json_encode($data);
exit;

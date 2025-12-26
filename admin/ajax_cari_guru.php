<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$keyword = $_GET['q'] ?? '';

if ($keyword == '') {
    echo json_encode([]);
    exit;
}

$keyword = "%".$keyword."%";

$stmt = $conn->prepare("SELECT id, nama FROM guru WHERE nama LIKE ? ORDER BY nama ASC LIMIT 20");
$stmt->bind_param("s", $keyword);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($g = $res->fetch_assoc()) {
    $data[] = [
        "id" => $g['id'],
        "nama" => $g['nama']
    ];
}

echo json_encode($data);
exit;

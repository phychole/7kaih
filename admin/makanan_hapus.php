<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);

$del = $conn->prepare("DELETE FROM jenis_makanan WHERE id=?");
$del->bind_param("i", $id);
$del->execute();

header("Location: makanan.php?msg=deleted");
exit;

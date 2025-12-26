<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);

$del = $conn->prepare("DELETE FROM jenis_masyarakat WHERE id=?");
$del->bind_param("i", $id);
$del->execute();

header("Location: masyarakat.php?msg=deleted");
exit;

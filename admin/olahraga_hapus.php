<?php
require '../config.php';
require '../auth.php';
if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);
$conn->query("DELETE FROM jenis_olahraga WHERE id=$id");
header("Location: olahraga.php");
exit;

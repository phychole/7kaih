<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: guru.php?msg=err"); exit; }

$stmt = $conn->prepare("DELETE FROM guru WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: guru.php?msg=del");
    exit;
}

header("Location: guru.php?msg=err");
exit;

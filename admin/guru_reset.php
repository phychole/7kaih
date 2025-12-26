<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: guru.php?msg=err"); exit; }

$newhash = password_hash("123456", PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE guru SET password=? WHERE id=?");
$stmt->bind_param("si", $newhash, $id);

if ($stmt->execute()) {
    header("Location: guru.php?msg=reset");
    exit;
}

header("Location: guru.php?msg=err");
exit;

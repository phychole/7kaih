<?php
require '../config.php';
require '../auth.php';
if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);
$conn->query("DELETE FROM users WHERE id=$id");

header("Location: user.php?msg=deleted");
exit;

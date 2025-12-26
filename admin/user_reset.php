<?php
require '../config.php';
require '../auth.php';
if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);
$newpass = password_hash("123456", PASSWORD_DEFAULT);

$conn->query("UPDATE users SET password='$newpass' WHERE id=$id");

header("Location: user.php?msg=reset");
exit;

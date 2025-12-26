<?php
require '../config.php';
require '../auth.php';
if ($_SESSION['role'] != 'admin') exit;

$id = $_GET['id'];
$conn->query("DELETE FROM siswa WHERE id=$id");

header("Location: siswa.php");
exit;

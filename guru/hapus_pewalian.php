<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') exit;

$id = (int)$_GET['id'];

$conn->query("DELETE FROM guru_wali_siswa WHERE id=$id");

header("Location: pewalian_list.php?msg=deleted");
exit;

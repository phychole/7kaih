<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

header("Location: dashboard.php");
exit;

<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
  header("Location: ../login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Panel Guru</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + FontAwesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://kit.fontawesome.com/a2d9d6c36e.js" crossorigin="anonymous"></script>

  <style>
    :root{
      --navh: 56px;
      --sbw: 220px;
      --sb: #1e1e2d;
      --sbHover: #34344a;
      --bg: #f5f6fa;
    }

    html, body{ height:100%; }
    body{ background: var(--bg); overflow-x:hidden; }

    /* TOP NAV */
    .topnav{
      height: var(--navh);
      z-index: 2000;
    }
  </style>
</head>

<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-dark bg-dark fixed-top shadow-sm topnav">
  <div class="container-fluid">

    <!-- Toggle Sidebar: di mobile muncul tulisan Menu -->
    <button class="btn btn-outline-light d-flex align-items-center gap-2"
            id="sidebarToggle"
            type="button"
            aria-label="Buka Menu">

      <i class="fas fa-bars"></i>
      <span class="d-inline d-lg-none fw-semibold">Menu</span>
    </button>

    <span class="navbar-brand mb-0">Panel Guru</span>

    <div class="d-flex align-items-center gap-2">
      <span class="text-white d-none d-sm-inline">
        <?= htmlspecialchars($_SESSION['nama'] ?? '-') ?>
      </span>
      <a href="../logout.php" class="btn btn-danger btn-sm">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>

  </div>
</nav>

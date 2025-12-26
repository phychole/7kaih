<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://kit.fontawesome.com/a2d9d6c36e.js" crossorigin="anonymous"></script>

  <style>
    :root{
      --sbw: 250px;
      --navh: 56px;
      --bg: #f5f6fa;
      --sb: #1e1e2d;
      --sbHover: #34344a;
    }
    html, body { height: 100%; }
    body { background: var(--bg); overflow-x: hidden; }

    .topnav{ height: var(--navh); z-index: 2000; }

    .sidebar{
      width: var(--sbw);
      background: var(--sb);
      color: #fff;
      position: fixed;
      top: var(--navh);
      left: 0;
      bottom: 0;
      overflow-y: auto;
      padding: 10px;
      transition: .25s ease;
      z-index: 1500;
      pointer-events: auto;
    }
    /* desktop collapse */
    body.sidebar-collapsed .sidebar{ left: calc(var(--sbw) * -1); }

    /* backdrop (mobile) */
    .sidebar-backdrop{
      display: none;
      position: fixed;
      top: var(--navh);
      left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,.35);
      z-index: 1400;
    }
    body.sidebar-open .sidebar-backdrop{ display: block; }

    .sidebar .logo{
      padding: 12px 14px;
      font-weight: 700;
      border-radius: 10px;
      background: rgba(0,0,0,.18);
      margin-bottom: 10px;
    }
    .sidebar a{
      text-decoration: none;
      color: #fff;
      display: block;
      padding: 10px 14px;
      border-radius: 10px;
      margin: 4px 0;
    }
    .sidebar a:hover{ background: var(--sbHover); }
    .sidebar a.active{ background: var(--sbHover); }

    .menu-toggle{
      width: 100%;
      background: none;
      border: 0;
      color: #fff;
      padding: 10px 14px;
      border-radius: 10px;
      margin-top: 6px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
    }
    .menu-toggle:hover{ background: var(--sbHover); }

    .submenu{
      display: none;
      margin-left: 10px;
      padding-left: 8px;
      border-left: 2px solid rgba(255,255,255,.15);
    }
    .submenu a{ padding-left: 18px; }

    .content{
      margin-left: var(--sbw);
      padding: calc(var(--navh) + 18px) 20px 20px 20px;
      position: relative;
      z-index: 1;
      max-width: 100%;
    }
    body.sidebar-collapsed .content{ margin-left: 0; }

    @media (max-width: 992px){
      .content{ margin-left: 0; padding: calc(var(--navh) + 14px) 14px 14px; }
      /* default sidebar hidden on mobile */
      .sidebar{ left: calc(var(--sbw) * -1); }
      body.sidebar-open .sidebar{ left: 0; }
      /* jangan pakai sidebar-collapsed untuk mobile */
      body.sidebar-collapsed .sidebar{ left: calc(var(--sbw) * -1); }
    }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark fixed-top shadow-sm topnav">
  <div class="container-fluid">
    <button class="btn btn-outline-light me-2" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </button>

    <span class="navbar-brand mb-0">Admin Panel</span>

    <div class="d-flex align-items-center gap-2">
      <span class="text-white d-none d-sm-inline"><?= htmlspecialchars($_SESSION['nama'] ?? '-') ?></span>
      <a href="../logout.php" class="btn btn-danger btn-sm">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

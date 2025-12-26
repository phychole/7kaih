<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kurikulum') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Kurikulum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & FontAwesome & ChartJS -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a2d9d6c36e.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            overflow-x: hidden;
            background: #f5f7fb;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* TOP NAVBAR */
        .navbar-kur {
            background: #1f2933; /* dark grey */
            height: 56px;
        }
        .navbar-kur .navbar-brand {
            color: #fff !important;
            font-weight: 600;
        }
        .navbar-kur .text-white {
            color: #e5e7eb !important;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: #111827; /* dark */
            color: white;
            position: fixed;
            top: 56px;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: 0.3s;
            z-index: 9999;
        }

        .sidebar.hide {
            left: -260px;
        }

        .sidebar a {
            color: #e5e7eb;
            text-decoration: none;
            display: block;
            padding: 10px 18px;
            border-radius: 6px;
            margin: 2px 8px;
            font-size: 0.95rem;
        }

        .sidebar a:hover {
            background: #374151;
        }

        .sidebar a.active {
            background: #2563eb;
            color: #fff;
        }

        .sidebar .logo {
            padding: 14px 18px;
            font-size: 1.1rem;
            border-bottom: 1px solid #374151;
        }

        /* CONTENT */
        .content {
            margin-left: 250px;
            padding: 80px 25px 30px 25px;
            width: calc(100% - 250px);
            transition: 0.3s;
        }

        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                width: 100%;
                padding: 80px 15px 30px 15px;
            }
        }

        .card-kur {
            border-radius: 12px;
        }

        .card-kur h6 {
            font-size: 0.9rem;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #6b7280;
        }

        .card-kur .display-6 {
            font-size: 1.8rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-kur fixed-top shadow-sm">
    <div class="container-fluid">

        <!-- Toggle Sidebar (if you want untuk mobile, tinggal tambahkan JS) -->
        <button class="btn btn-outline-light btn-sm me-2" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <span class="navbar-brand">
            <i class="fas fa-chart-line me-2"></i> Panel Kurikulum
        </span>

        <span class="text-white ms-auto me-3">
            <?= htmlspecialchars($_SESSION['nama'] ?? 'Kurikulum'); ?>
        </span>

        <a href="../logout.php" class="btn btn-sm btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

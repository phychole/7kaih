<div class="sidebar" id="sidebar">
    <div class="logo fw-bold">
        <i class="fa-solid fa-book me-2"></i> Kurikulum
    </div>

    <a href="index.php"
       class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge-high me-2"></i> Dashboard
    </a>

    <a href="rekap.php"
       class="<?= basename($_SERVER['PHP_SELF']) == 'rekap.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-table-list me-2"></i> Rekap Jurnal
    </a>

    <hr class="text-white">

    <a href="../logout.php" class="text-danger">
        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
    </a>
</div>

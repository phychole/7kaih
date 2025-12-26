<?php
// file: partials/navbar.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Jurnal 7 Kebiasaan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['role'])): ?>
          <li class="nav-item">
            <span class="navbar-text me-3">
              <?= htmlspecialchars($_SESSION['nama'] ?? '') ?> (<?= $_SESSION['role'] ?>)
            </span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/jurnal7/logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/jurnal7/login.php">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

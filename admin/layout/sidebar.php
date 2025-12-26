<?php
$current = basename($_SERVER['PHP_SELF']);
function active($file, $current){ return $file === $current ? 'active' : ''; }
?>

<div class="sidebar" id="sidebar">

  <div class="logo">
    <i class="fa-solid fa-user-shield me-2"></i> Admin
  </div>

  <a href="index.php" class="<?=active('index.php',$current)?>">
    <i class="fas fa-house me-2"></i> Dashboard
  </a>

  <button class="menu-toggle" data-target="sm1" type="button">
    <span><i class="fas fa-layer-group me-2"></i> Data Utama</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="submenu" id="sm1">
    <a href="guru.php" class="<?=active('guru.php',$current)?>"><i class="fas fa-chalkboard-teacher me-2"></i> Guru</a>
    <a href="siswa.php" class="<?=active('siswa.php',$current)?>"><i class="fas fa-user-graduate me-2"></i> Siswa</a>
    <a href="kelas.php" class="<?=active('kelas.php',$current)?>"><i class="fas fa-school me-2"></i> Kelas</a>
    <a href="siswa_wali.php" class="<?=active('siswa_wali.php',$current)?>"><i class="fas fa-user-friends me-2"></i> Guru Wali Siswa</a>
  </div>

  <button class="menu-toggle" data-target="sm2" type="button">
    <span><i class="fas fa-cogs me-2"></i> Master Kegiatan</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="submenu" id="sm2">
    <a href="olahraga.php" class="<?=active('olahraga.php',$current)?>"><i class="fas fa-dumbbell me-2"></i> Olahraga</a>
    <a href="belajar.php" class="<?=active('belajar.php',$current)?>"><i class="fas fa-book-open me-2"></i> Belajar</a>
    <a href="makanan.php" class="<?=active('makanan.php',$current)?>"><i class="fas fa-apple-alt me-2"></i> Makanan Sehat</a>
    <a href="masyarakat.php" class="<?=active('masyarakat.php',$current)?>"><i class="fas fa-people-arrows me-2"></i> Bermasyarakat</a>
  </div>

  <button class="menu-toggle" data-target="sm3" type="button">
    <span><i class="fas fa-list-check me-2"></i> 7 Kebiasaan</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="submenu" id="sm3">
    <a href="kegiatan.php" class="<?=active('kegiatan.php',$current)?>"><i class="fas fa-clipboard-list me-2"></i> Kegiatan Utama</a>
    <a href="detail_kegiatan.php" class="<?=active('detail_kegiatan.php',$current)?>"><i class="fas fa-align-left me-2"></i> Detail Kegiatan</a>
  </div>

  <button class="menu-toggle" data-target="sm4" type="button">
    <span><i class="fas fa-chart-bar me-2"></i> Laporan</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="submenu" id="sm4">
    <a href="rekap.php" class="<?=active('rekap.php',$current)?>"><i class="fas fa-file-alt me-2"></i> Rekap Jurnal</a>
  </div>

  <button class="menu-toggle" data-target="sm5" type="button">
    <span><i class="fas fa-users-cog me-2"></i> User Management</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="submenu" id="sm5">
    <a href="user.php" class="<?=active('user.php',$current)?>"><i class="fas fa-users me-2"></i> Semua User</a>
    <a href="ganti_password.php" class="<?=active('ganti_password.php',$current)?>"><i class="fas fa-key me-2"></i> Ganti Password</a>
  </div>

  <hr class="text-white my-3">

  <a href="../logout.php" class="text-danger">
    <i class="fas fa-sign-out-alt me-2"></i> Logout
  </a>

</div>

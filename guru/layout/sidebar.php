<?php
// aktif menu
$current = basename($_SERVER['PHP_SELF']);
function active($file, $current){
    return $file === $current ? 'active' : '';
}
?>

<style>
/* ===== SIDEBAR GURU RESPONSIVE FINAL ===== */
:root{
  --navh: 56px;
  --sbw: 220px;
  --sb: #1e1e2d;
  --sbHover: #34344a;
}

.sidebar {
  width: var(--sbw);
  background: var(--sb);
  color: #fff;
  position: fixed;
  top: var(--navh);
  bottom: 0;
  left: 0;
  overflow-y: auto;
  padding: 10px 0;
  z-index: 1500;
  transition: left .25s ease;
}

/* desktop: content geser */
.content{
  margin-left: var(--sbw);
  padding: calc(var(--navh) + 18px) 20px 20px;
}

/* link */
.sidebar a{
  display: block;
  padding: 10px 18px;
  color: #fff;
  text-decoration: none;
  font-size: 15px;
  border-radius: 10px;
  margin: 4px 10px;
}
.sidebar a:hover{ background: var(--sbHover); }
.sidebar a.active{ background: var(--sbHover); }

/* title group */
.menu-title{
  font-size: 12px;
  text-transform: uppercase;
  opacity: .65;
  padding: 10px 18px 5px;
  margin-top: 8px;
}

/* BACKDROP (mobile only) */
.sidebar-backdrop{
  display: none;
  position: fixed;
  top: var(--navh);
  left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,.4);
  z-index: 1400;
}

/* ===== MOBILE ===== */
@media (max-width: 992px){
  .content{
    margin-left: 0;
    padding: calc(var(--navh) + 14px) 14px 14px;
  }

  /* sidebar default hidden */
  .sidebar{ left: calc(var(--sbw) * -1); }

  /* when opened */
  body.sidebar-open .sidebar{ left: 0; }
  body.sidebar-open .sidebar-backdrop{ display: block; }
}
</style>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebarGuru">

  <div class="menu-title">Menu Utama</div>

  <a href="index.php" class="<?= active('index.php',$current) ?>">
    <i class="fas fa-home me-2"></i> Dashboard
  </a>

  <a href="wali_kelas.php" class="<?= active('wali_kelas.php',$current) ?>">
    <i class="fas fa-users me-2"></i> Wali Kelas
  </a>

  <!-- Rekap Bulanan (opsional, kalau sudah dibuat) -->
  <a href="rekap_bulanan.php" class="<?= active('rekap_bulanan.php',$current) ?>">
    <i class="fas fa-calendar-alt me-2"></i> Rekap Bulanan
  </a>

  <div class="menu-title">Guru Wali</div>

  <a href="pewalian_tambah.php" class="<?= active('pewalian_tambah.php',$current) ?>">
    <i class="fas fa-user-plus me-2"></i> Tambah Siswa Wali
  </a>

  <a href="pewalian_list.php" class="<?= active('pewalian_list.php',$current) ?>">
    <i class="fas fa-user-friends me-2"></i> Data Siswa Wali
  </a>
  <a href="rekap_bulanan_wali.php">
  <i class="fas fa-calendar-alt me-2"></i> Rekap Bulanan Siswa Wali
</a>

  <div class="menu-title">Pengaturan</div>

  <a href="ganti_password.php" class="<?= active('ganti_password.php',$current) ?>">
    <i class="fas fa-key me-2"></i> Ganti Password
  </a>

  <a href="../logout.php" class="text-danger">
    <i class="fas fa-sign-out-alt me-2"></i> Logout
  </a>

</div>

<!-- BACKDROP (mobile) -->
<div class="sidebar-backdrop" id="sidebarBackdropGuru"></div>

<script>
(function(){
  // tombol toggle harus ada di header: id="sidebarToggle"
  const btn = document.getElementById('sidebarToggle');
  const backdrop = document.getElementById('sidebarBackdropGuru');

  // toggle sidebar (mobile)
  btn?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
  });

  // klik backdrop menutup sidebar
  backdrop?.addEventListener('click', () => {
    document.body.classList.remove('sidebar-open');
  });

  // auto close saat klik menu (mobile)
  document.querySelectorAll('#sidebarGuru a').forEach(a => {
    a.addEventListener('click', () => {
      if (window.matchMedia('(max-width: 992px)').matches) {
        document.body.classList.remove('sidebar-open');
      }
    });
  });

  // kalau resize ke desktop, pastikan sidebar-open dibersihkan
  window.addEventListener('resize', () => {
    if (!window.matchMedia('(max-width: 992px)').matches) {
      document.body.classList.remove('sidebar-open');
    }
  });
})();
</script>

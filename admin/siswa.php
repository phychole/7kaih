<?php
include 'layout/header.php';
include 'layout/sidebar.php';

if ($_SESSION['role'] != 'admin') exit;

// ==========================
// CSRF TOKEN
// ==========================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// ==========================
// PAGINATION
// ==========================
$limit = 10;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// ==========================
// FILTER KELAS
// ==========================
$filter_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

// ==========================
// PENCARIAN
// ==========================
$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$keyword_safe = $conn->real_escape_string($keyword);

// ==========================
// WHERE CLAUSE
// ==========================
$where = "WHERE 1=1";

if ($filter_kelas > 0) {
    $where .= " AND s.id_kelas = $filter_kelas";
}

if ($keyword !== '') {
    $where .= " AND (s.nama LIKE '%$keyword_safe%'
                OR s.nisn LIKE '%$keyword_safe%'
                OR s.jenis_kelamin LIKE '%$keyword_safe%'
                OR s.agama LIKE '%$keyword_safe%')";
}

// ==========================
// HITUNG TOTAL DATA
// ==========================
$sqlCount = $conn->query("
    SELECT COUNT(*) AS total
    FROM siswa s
    $where
");
$totalData = ($sqlCount) ? (int)$sqlCount->fetch_assoc()['total'] : 0;
$totalPage = max(1, (int)ceil($totalData / $limit));

// ==========================
// AMBIL DATA SISWA
// ==========================
$sqlSiswa = $conn->query("
    SELECT s.*, k.nama_kelas
    FROM siswa s
    JOIN kelas k ON s.id_kelas = k.id
    $where
    ORDER BY s.nama ASC
    LIMIT $start, $limit
");

// ==========================
// AMBIL DATA KELAS UNTUK FILTER
// ==========================
$kelasList = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-users"></i> Data Siswa</h3>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'import'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    Import selesai! Ditambahkan: <b><?= (int)($_GET['add'] ?? 0) ?></b>, Dilewati: <b><?= (int)($_GET['skip'] ?? 0) ?></b>
</div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted_all'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> Semua data siswa berhasil dihapus.
</div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'resetpass_all'): ?>
<div class="alert alert-info">
    <i class="fas fa-check-circle"></i> Password semua siswa berhasil di-reset ke NISN masing-masing (terenkripsi).
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'csrf'): ?>
<div class="alert alert-danger">
    <i class="fas fa-times-circle"></i> Aksi ditolak (CSRF tidak valid).
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'delete_all_failed'): ?>
<div class="alert alert-danger">
    <i class="fas fa-times-circle"></i> Gagal menghapus semua siswa. Cek relasi tabel (FK) atau data jurnal.
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'resetpass_all_failed'): ?>
<div class="alert alert-danger">
    <i class="fas fa-times-circle"></i> Gagal reset password semua siswa.
</div>
<?php endif; ?>

<!-- FILTER & SEARCH -->
<form class="row mb-3" method="GET">

    <!-- FILTER KELAS -->
    <div class="col-md-3 mb-2">
        <select name="kelas" class="form-control">
            <option value="0">-- Semua Kelas --</option>
            <?php while ($k = $kelasList->fetch_assoc()): ?>
                <option value="<?= (int)$k['id'] ?>" <?= ($filter_kelas==(int)$k['id']?'selected':'') ?>>
                    <?= htmlspecialchars($k['nama_kelas']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- SEARCH -->
    <div class="col-md-4 mb-2">
        <input type="text" name="cari" class="form-control"
               placeholder="Cari nama / NISN / agama..."
               value="<?= htmlspecialchars($keyword) ?>">
    </div>

    <div class="col-md-2 mb-2">
        <button class="btn btn-dark w-100" type="submit">
            <i class="fas fa-search"></i> Cari
        </button>
    </div>

    <div class="col-md-3 mb-2 text-end">

        <a href="siswa_import.php" class="btn btn-primary mb-2">
            <i class="fas fa-upload"></i> Import CSV
        </a>

        <a href="siswa_export.php?kelas=<?= $filter_kelas ?>&cari=<?= urlencode($keyword) ?>"
           class="btn btn-success mb-2">
            <i class="fas fa-download"></i> Export CSV
        </a>

        <a href="siswa_tambah.php" class="btn btn-primary mb-2">
            <i class="fas fa-plus"></i> Tambah Siswa
        </a>

        <!-- RESET PASSWORD SEMUA (NISN) -->
        <form action="siswa_resetpass_all.php" method="POST" class="d-inline" onsubmit="return confirmResetPassAll();">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn btn-warning mb-2">
                <i class="fas fa-key"></i> Reset Password Semua (NISN)
            </button>
        </form>

        <!-- HAPUS SEMUA SISWA -->
        <form action="siswa_hapus_semua.php" method="POST" class="d-inline" onsubmit="return confirmHapusSemua();">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn btn-danger mb-2">
                <i class="fas fa-trash"></i> Hapus Semua Siswa
            </button>
        </form>

    </div>
</form>

<script>
function confirmHapusSemua(){
    if(!confirm("PERINGATAN! Anda akan menghapus SEMUA DATA SISWA. Lanjutkan?")) return false;
    const ketik = prompt("Ketik: HAPUS SEMUA untuk konfirmasi:");
    if(ketik !== "HAPUS SEMUA") {
        alert("Dibatalkan.");
        return false;
    }
    return confirm("Konfirmasi terakhir: yakin ingin menghapus SEMUA siswa?");
}

function confirmResetPassAll(){
    if(!confirm("PERINGATAN! Semua password siswa akan di-reset ke NISN masing-masing. Lanjutkan?")) return false;
    const ketik = prompt("Ketik: RESET NISN untuk konfirmasi:");
    if(ketik !== "RESET NISN") {
        alert("Dibatalkan.");
        return false;
    }
    return confirm("Konfirmasi terakhir: yakin reset password SEMUA siswa?");
}
</script>

<!-- TABEL SISWA -->
<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Foto</th>
    <th>NISN</th>
    <th>Nama</th>
    <th>Kelas</th>
    <th>JK</th>
    <th>Agama</th>
    <th width="180">Aksi</th>
</tr>
</thead>

<tbody>

<?php if ($totalData == 0): ?>
<tr>
    <td colspan="7" class="text-center text-danger">Tidak ada data siswa.</td>
</tr>
<?php endif; ?>

<?php while($d = $sqlSiswa->fetch_assoc()): ?>
<tr>
    <td>
        <img src="../uploads/siswa/<?= htmlspecialchars($d['foto']) ?>"
             style="width:55px;height:55px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
    </td>
    <td><?= htmlspecialchars($d['nisn']) ?></td>
    <td><?= htmlspecialchars($d['nama']) ?></td>
    <td><?= htmlspecialchars($d['nama_kelas']) ?></td>
    <td><?= htmlspecialchars($d['jenis_kelamin']) ?></td>
    <td><?= htmlspecialchars($d['agama']) ?></td>
    <td>
        <a href="siswa_edit.php?id=<?= (int)$d['id'] ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>

        <a href="siswa_hapus.php?id=<?= (int)$d['id'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Yakin ingin menghapus siswa ini?')">
            <i class="fas fa-trash"></i> Hapus
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<?php
// ==========================
// PAGINATION BLOK 10 HALAMAN
// ==========================
$range = 10; // jumlah halaman per blok
$startPage = (int)(floor(($page - 1) / $range) * $range) + 1;
$endPage   = min($startPage + $range - 1, $totalPage);
?>

<nav aria-label="pagination">
<ul class="pagination justify-content-center">

<?php if ($startPage > 1): ?>
<li class="page-item">
    <a class="page-link"
       href="?page=<?= $startPage - 1 ?>&kelas=<?= $filter_kelas ?>&cari=<?= urlencode($keyword) ?>">
        « Prev
    </a>
</li>
<?php endif; ?>

<?php for ($i = $startPage; $i <= $endPage; $i++): ?>
<li class="page-item <?= ($page == $i ? 'active' : '') ?>">
    <a class="page-link"
       href="?page=<?= $i ?>&kelas=<?= $filter_kelas ?>&cari=<?= urlencode($keyword) ?>">
        <?= $i ?>
    </a>
</li>
<?php endfor; ?>

<?php if ($endPage < $totalPage): ?>
<li class="page-item">
    <a class="page-link"
       href="?page=<?= $endPage + 1 ?>&kelas=<?= $filter_kelas ?>&cari=<?= urlencode($keyword) ?>">
        Next »
    </a>
</li>
<?php endif; ?>

</ul>
</nav>

</div>

<?php include 'layout/footer.php'; ?>

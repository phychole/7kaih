<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

// ==========================
// PAGINATION SETTING
// ==========================
$limit = 10; 
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// ==========================
// SEARCH
// ==========================
$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$keyword_safe = $conn->real_escape_string($keyword);

$where = "";
if ($keyword != '') {
    $where = "
        WHERE nama LIKE '%$keyword_safe%' 
        OR email LIKE '%$keyword_safe%' 
        OR agama LIKE '%$keyword_safe%' 
        OR jenis_kelamin LIKE '%$keyword_safe%'
    ";
}

// Count data
$sqlCount = $conn->query("SELECT COUNT(*) AS total FROM guru $where");
$totalRow = ($sqlCount) ? $sqlCount->fetch_assoc()['total'] : 0;
$totalPage = max(1, ceil($totalRow / $limit));

// Ambil data guru
$sqlGuru = $conn->query("
    SELECT * FROM guru
    $where 
    ORDER BY nama ASC 
    LIMIT $start, $limit
");
?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-chalkboard-user"></i> Data Guru</h3>

<!-- NOTIFIKASI IMPORT -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'import'): ?>
<div class="alert alert-success">
    Import selesai! Ditambahkan: <?= $_GET['add'] ?>, Dilewati: <?= $_GET['skip'] ?>
</div>
<?php endif; ?>

<!-- NOTIFIKASI RESET -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'reset'): ?>
<div class="alert alert-info">Password berhasil direset menjadi: <b>123456</b></div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'hapus_semua'): ?>
<div class="alert alert-warning">
    <i class="fas fa-check-circle"></i>
    Semua data guru berhasil dihapus.
</div>
<?php endif; ?>


<!-- FORM SEARCH -->
<form class="row mb-3" method="GET">
    <div class="col-md-4 mb-2">
        <input type="text" name="cari" class="form-control"
               placeholder="Cari nama / email / agama..."
               value="<?= htmlspecialchars($keyword) ?>">
    </div>

    <div class="col-md-2 mb-2">
        <button class="btn btn-dark w-100" type="submit">
            <i class="fas fa-search"></i> Cari
        </button>
    </div>

    <div class="col-md-2 mb-2">
        <a href="guru.php" class="btn btn-secondary w-100">
            <i class="fas fa-rotate"></i> Reset
        </a>
    </div>
<div class="col-md-4 text-end mb-2">
    <a href="guru_import.php" class="btn btn-primary">
        <i class="fas fa-upload"></i> Import CSV
    </a>
    <a href="guru_export.php" class="btn btn-success">
        <i class="fas fa-download"></i> Export CSV
    </a>
    <a href="guru_tambah.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Guru
    </a>

    <!-- HAPUS SEMUA -->
    <a href="guru_hapus_semua.php"
       class="btn btn-danger"
       onclick="return confirm(
        'PERINGATAN!\n\nSemua data guru akan DIHAPUS.\nAksi ini tidak bisa dibatalkan.\n\nLanjutkan?'
       )">
        <i class="fas fa-trash-alt"></i> Hapus Semua
    </a>
</div>

</form>


<!-- TABEL DATA GURU -->
<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Nama</th>
    <th>Email</th>
    <th>Jenis Kelamin</th>
    <th>Agama</th>
    <th width="260px">Aksi</th>
</tr>
</thead>
<tbody>

<?php if ($totalRow == 0): ?>
<tr>
    <td colspan="5" class="text-center text-danger">Tidak ada data guru.</td>
</tr>
<?php endif; ?>

<?php while($d = $sqlGuru->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($d['nama']) ?></td>
    <td><?= htmlspecialchars($d['email']) ?></td>
    <td><?= htmlspecialchars($d['jenis_kelamin']) ?></td>
    <td><?= htmlspecialchars($d['agama']) ?></td>

    <td>
        <a href="guru_edit.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>

        <a href="guru_reset.php?id=<?= $d['id'] ?>" 
           class="btn btn-info btn-sm"
           onclick="return confirm('Reset password guru ini menjadi 123456?')">
            <i class="fas fa-key"></i> Reset
        </a>

        <a href="guru_hapus.php?id=<?= $d['id'] ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Yakin ingin menghapus guru ini?')">
            <i class="fas fa-trash"></i> Hapus
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>


<!-- PAGINATION -->
<nav aria-label="Page navigation">
<ul class="pagination">

<?php if ($page > 1): ?>
    <li class="page-item">
        <a class="page-link" href="?page=<?= $page-1 ?>&cari=<?= urlencode($keyword) ?>">« Prev</a>
    </li>
<?php endif; ?>

<?php for ($i = 1; $i <= $totalPage; $i++): ?>
    <li class="page-item <?= ($page == $i ? 'active' : '') ?>">
        <a class="page-link" href="?page=<?= $i ?>&cari=<?= urlencode($keyword) ?>">
            <?= $i ?>
        </a>
    </li>
<?php endfor; ?>

<?php if ($page < $totalPage): ?>
    <li class="page-item">
        <a class="page-link" href="?page=<?= $page+1 ?>&cari=<?= urlencode($keyword) ?>">Next »</a>
    </li>
<?php endif; ?>

</ul>
</nav>

</div>

<?php include 'layout/footer.php'; ?>

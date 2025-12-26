<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

if ($_SESSION['role'] != 'admin') exit;

// ==========================
// PENCARIAN
// ==========================
$keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$keyword_safe = $conn->real_escape_string($keyword);

$where = "";
if ($keyword != '') {
    $where = "WHERE nama LIKE '%$keyword_safe%'";
}

// ==========================
// PAGINATION
// ==========================
$limit = 10;
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $limit;

$sqlCount = $conn->query("SELECT COUNT(*) AS total FROM jenis_olahraga $where");
$totalData = $sqlCount->fetch_assoc()['total'];
$totalPage = max(1, ceil($totalData / $limit));

$q = $conn->query("
    SELECT * FROM jenis_olahraga 
    $where 
    ORDER BY nama ASC 
    LIMIT $start, $limit
");
?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-dumbbell"></i> Master Jenis Olahraga</h3>
<?php if (isset($_GET['msg']) && $_GET['msg']=='import'): ?>
<div class="alert alert-success">
    Import selesai! Ditambahkan: <b><?= $_GET['add'] ?></b>, 
    Dilewati (duplikat/invalid): <b><?= $_GET['skip'] ?></b>.
</div>
<?php endif; ?>

<!-- SEARCH -->
<form class="row mb-3" method="GET">
    <div class="col-md-4">
        <input type="text" name="cari" class="form-control" 
               placeholder="Cari jenis olahraga..."
               value="<?= htmlspecialchars($keyword) ?>">
    </div>

    <div class="col-md-2">
        <button class="btn btn-dark w-100">
            <i class="fas fa-search"></i> Cari
        </button>
    </div>

    <div class="col-md-2">
        <a href="olahraga.php" class="btn btn-secondary w-100">
            <i class="fas fa-rotate"></i> Reset
        </a>
    </div>

   <div class="col-md-4 text-end">
    <a href="olahraga_import.php" class="btn btn-primary">
        <i class="fas fa-upload"></i> Import CSV
    </a>
    <a href="olahraga_tambah.php" class="btn btn-success">
        <i class="fas fa-plus"></i> Tambah Jenis
    </a>
</div>

</form>

<div class="card shadow">
<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Nama</th>
    <th width="180px">Aksi</th>
</tr>
</thead>

<tbody>

<?php if ($totalData == 0): ?>
<tr>
    <td colspan="2" class="text-center text-danger">Tidak ada data.</td>
</tr>
<?php endif; ?>

<?php while($row = $q->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td>
        <a href="olahraga_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>

        <a href="olahraga_hapus.php?id=<?= $row['id'] ?>" 
           onclick="return confirm('Hapus data ini?')" 
           class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Hapus
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</div>
</div>

<!-- PAGINATION -->
<nav class="mt-3">
<ul class="pagination">

<?php if ($page > 1): ?>
<li class="page-item">
    <a class="page-link" href="?page=<?= $page - 1 ?>&cari=<?= urlencode($keyword) ?>">« Prev</a>
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
    <a class="page-link" href="?page=<?= $page + 1 ?>&cari=<?= urlencode($keyword) ?>">Next »</a>
</li>
<?php endif; ?>

</ul>
</nav>

</div>

<?php include 'layout/footer.php'; ?>

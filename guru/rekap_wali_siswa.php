<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') exit;

$id_guru = $_SESSION['user_id'];

// --- FILTER ---
$filter_tanggal  = $_GET['tanggal'] ?? '';
$filter_kegiatan = $_GET['kegiatan'] ?? '';
$filter_siswa    = $_GET['nama'] ?? '';

// Ambil daftar siswa binaan
$sql_siswa = "
    SELECT s.id, s.nama, s.nisn, k.nama_kelas
    FROM guru_wali_siswa gw
    JOIN siswa s ON gw.id_siswa = s.id
    JOIN kelas k ON s.id_kelas = k.id
    WHERE gw.id_guru = $id_guru
";

if ($filter_siswa != '') {
    $safe_nama = $conn->real_escape_string($filter_siswa);
    $sql_siswa .= " AND s.nama LIKE '%$safe_nama%' ";
}

$sql_siswa .= " ORDER BY s.nama ASC";

$list_siswa = $conn->query($sql_siswa);

// Ambil list kegiatan
$list_kegiatan = $conn->query("SELECT * FROM jenis_kegiatan ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Rekap Jurnal Siswa Wali</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://kit.fontawesome.com/a2d9d6c36e.js" crossorigin="anonymous"></script>

<style>
.table td { vertical-align: top; }
</style>
</head>
<body>

<div class="container mt-4">

<!-- Header -->
<div class="d-flex justify-content-between mb-3">
    <h3><i class="fas fa-user-friends"></i> Rekap Jurnal Siswa Wali</h3>

    <div>
        <a href="export_wali_excel.php?<?= http_build_query($_GET) ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>

        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- FILTER -->
<div class="card shadow-sm mb-3">
<div class="card-body">
<form method="GET" class="row g-3">

    <div class="col-md-3">
        <label class="form-label"><b>Tanggal</b></label>
        <input type="date" name="tanggal" class="form-control"
               value="<?= htmlspecialchars($filter_tanggal) ?>">
    </div>

    <div class="col-md-3">
        <label class="form-label"><b>Jenis Kegiatan</b></label>
        <select name="kegiatan" class="form-control">
            <option value="">Semua</option>
            <?php while($kg = $list_kegiatan->fetch_assoc()): ?>
            <option value="<?= $kg['id'] ?>" <?= ($filter_kegiatan == $kg['id'] ? 'selected' : '') ?>>
                <?= $kg['nama_kegiatan'] ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label"><b>Nama Siswa</b></label>
        <input type="text" name="nama" class="form-control" placeholder="Cari siswa..."
               value="<?= htmlspecialchars($filter_siswa) ?>">
    </div>

    <div class="col-md-3">
        <label class="form-label">&nbsp;</label><br>
        <button class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
        <a href="rekap_wali_siswa.php" class="btn btn-secondary">Reset</a>
    </div>

</form>
</div>
</div>

<!-- TABEL REKAP -->
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Nama</th>
    <th>NISN</th>
    <th>Kelas</th>
    <th>Kegiatan</th>
    <th>Tanggal</th>
    <th>Detail</th>
    <th>Status</th>
</tr>
</thead>
<tbody>

<?php if ($list_siswa->num_rows == 0): ?>
<tr>
    <td colspan="7" class="text-center text-danger">Tidak ada siswa binaan.</td>
</tr>
<?php endif; ?>

<?php while($s = $list_siswa->fetch_assoc()): ?>

<?php
// Ambil jurnal tiap siswa
$sql_j = "
    SELECT j.*, jk.nama_kegiatan 
    FROM jurnal_siswa j
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    WHERE j.id_siswa = {$s['id']}
";

if ($filter_tanggal != '') {
    $safe_tgl = $conn->real_escape_string($filter_tanggal);
    $sql_j .= " AND j.tanggal = '$safe_tgl' ";
}

if ($filter_kegiatan != '') {
    $sql_j .= " AND j.id_kegiatan = ".intval($filter_kegiatan)." ";
}

$sql_j .= " ORDER BY j.tanggal DESC";

$jurnal = $conn->query($sql_j);
?>

<?php if ($jurnal->num_rows == 0): ?>
<tr>
    <td><?= $s['nama'] ?></td>
    <td><?= $s['nisn'] ?></td>
    <td><?= $s['nama_kelas'] ?></td>
    <td colspan="4" class="text-center text-muted">Tidak ada jurnal</td>
</tr>
<?php endif; ?>

<?php while($d = $jurnal->fetch_assoc()): ?>
<tr>
    <td><?= $s['nama'] ?></td>
    <td><?= $s['nisn'] ?></td>
    <td><?= $s['nama_kelas'] ?></td>
    <td><?= $d['nama_kegiatan'] ?></td>
    <td><?= $d['tanggal'] ?></td>
    <td>
        <?php
        if ($d['jam_bangun']) echo "Bangun: ".$d['jam_bangun']."<br>";
        if ($d['ibadah']) echo "Ibadah: ".$d['ibadah']." (".$d['jam_ibadah'].")<br>";
        if ($d['olahraga']) echo "Olahraga: ".$d['olahraga']."<br>";
        if ($d['belajar']) echo "Belajar: ".$d['belajar']."<br>";
        if ($d['makanan_sehat']) echo "Makan Sehat: ".$d['makanan_sehat']."<br>";
        if ($d['masyarakat']) echo "Bermasyarakat: ".$d['masyarakat']."<br>";
        if ($d['jam_tidur']) echo "Tidur: ".$d['jam_tidur']."<br>";
        if ($d['catatan']) echo "<i>Catatan:</i> ".$d['catatan'];
        ?>
    </td>
    <td>
        <?php if ($d['nilai']=='Sudah'): ?>
            <span class="badge bg-success">Sudah</span>
        <?php else: ?>
            <span class="badge bg-danger">Belum</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

<?php endwhile; ?>

</tbody>
</table>

</div>

</body>
</html>

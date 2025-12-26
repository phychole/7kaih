<?php 
include 'layout/header.php';
include 'layout/sidebar.php';

if ($_SESSION['role'] != 'admin') exit;

// Ambil semua kelas
$kelas = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");

// Filter
$id_kelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : 0;
$tanggal  = $_GET['tanggal'] ?? date('Y-m-d');

$hasil = null;
$jumlah_data = 0;

// Query berdasarkan kondisi
if ($id_kelas > 0) {
    // Rekap per kelas
    $stmt = $conn->prepare("
        SELECT j.*, s.nama, s.nisn, k.nama_kelas, jk.nama_kegiatan 
        FROM jurnal_siswa j
        JOIN siswa s ON j.id_siswa = s.id
        JOIN kelas k ON s.id_kelas = k.id
        JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
        WHERE s.id_kelas = ? AND j.tanggal = ?
        ORDER BY s.nama ASC
    ");
    $stmt->bind_param("is", $id_kelas, $tanggal);
} else {
    // Rekap semua kelas pada tanggal tersebut
    $stmt = $conn->prepare("
        SELECT j.*, s.nama, s.nisn, k.nama_kelas, jk.nama_kegiatan 
        FROM jurnal_siswa j
        JOIN siswa s ON j.id_siswa = s.id
        JOIN kelas k ON s.id_kelas = k.id
        JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
        WHERE j.tanggal = ?
        ORDER BY k.nama_kelas ASC, s.nama ASC
    ");
    $stmt->bind_param("s", $tanggal);
}

$stmt->execute();
$hasil = $stmt->get_result();
$jumlah_data = $hasil->num_rows;

?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-clipboard-check"></i> Rekap Jurnal Siswa – Admin</h3>

<!-- FORM FILTER -->
<form class="row mb-4">

    <!-- PILIH KELAS -->
    <div class="col-md-4 mb-3">
        <label class="form-label"><b>Pilih Kelas (optional)</b></label>
        <select name="id_kelas" class="form-control">
            <option value="0">Semua Kelas</option>
            <?php while($k = $kelas->fetch_assoc()): ?>
            <option value="<?= $k['id'] ?>" <?= ($id_kelas == $k['id']) ? 'selected' : '' ?>>
                <?= $k['nama_kelas'] ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- PILIH TANGGAL -->
    <div class="col-md-4 mb-3">
        <label class="form-label"><b>Tanggal</b></label>
        <input type="date" name="tanggal" value="<?= $tanggal ?>" class="form-control" required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Tampilkan Rekap
        </button>
    </div>

</form>

<!-- TAMPILAN INFORMASI -->
<?php if ($jumlah_data == 0): ?>

<div class="alert alert-warning">
    <i class="fas fa-info-circle"></i> 
    Belum ada data jurnal pada tanggal <b><?= $tanggal ?></b>.
</div>

<?php else: ?>

<div class="card shadow">
<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Kelas</th>
    <th>Nama Siswa</th>
    <th>NISN</th>
    <th>Kegiatan</th>
    <th>Status / Nilai</th>
    <th>Catatan</th>
</tr>
</thead>

<tbody>
<?php while($d = $hasil->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($d['nama_kelas']) ?></td>
    <td><?= htmlspecialchars($d['nama']) ?></td>
    <td><?= htmlspecialchars($d['nisn']) ?></td>
    <td><?= htmlspecialchars($d['nama_kegiatan']) ?></td>
    <td><?= htmlspecialchars($d['nilai']) ?></td>
    <td><?= htmlspecialchars($d['catatan']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>

</div>
</div>

<?php endif; ?>

</div>

<?php include 'layout/footer.php'; ?>

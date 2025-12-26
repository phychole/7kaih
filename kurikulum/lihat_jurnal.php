<?php
include 'layout/header.php';
include 'layout/sidebar.php';

// --- Validasi siswa ---
$id_siswa = (int)($_GET['id_siswa'] ?? 0);
if ($id_siswa <= 0) exit("Siswa tidak valid.");

// --- Ambil data siswa ---
$s = $conn->query("
    SELECT s.*, k.nama_kelas 
    FROM siswa s 
    JOIN kelas k ON s.id_kelas = k.id
    WHERE s.id = $id_siswa
")->fetch_assoc();
if (!$s) exit("Data siswa tidak ditemukan.");

$filter_tanggal  = $_GET['tanggal'] ?? '';
$filter_kegiatan = $_GET['id_kegiatan'] ?? '';

// master kegiatan
$kegiatanRes = $conn->query("SELECT * FROM jenis_kegiatan ORDER BY nama_kegiatan ASC");

// query jurnal siswa
$sql = "
    SELECT j.*, jk.nama_kegiatan
    FROM jurnal_siswa j
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    WHERE j.id_siswa = $id_siswa
";
if ($filter_tanggal != '') {
    $safe = $conn->real_escape_string($filter_tanggal);
    $sql .= " AND j.tanggal = '$safe' ";
}
if ($filter_kegiatan != '') {
    $sql .= " AND j.id_kegiatan = " . intval($filter_kegiatan) . " ";
}
$sql .= " ORDER BY j.tanggal DESC, j.id DESC";

$jurnal = $conn->query($sql);
?>

<div class="content">

    <h3><i class="fas fa-user-graduate"></i> Jurnal Siswa</h3>
    <p class="text-muted">
        <b><?= htmlspecialchars($s['nama']) ?></b> (<?= htmlspecialchars($s['nisn']) ?>),
        Kelas: <b><?= htmlspecialchars($s['nama_kelas']) ?></b>
    </p>

    <a href="rekap.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali ke Rekap
    </a>

    <!-- TOMBOL EXPORT -->
    <a href="export_jurnal_siswa.php?<?= http_build_query($_GET) ?>"
       class="btn btn-success mb-3">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>

    <!-- FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">

                <div class="col-md-4">
                    <label class="form-label"><b>Tanggal</b></label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= htmlspecialchars($filter_tanggal) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label"><b>Kegiatan</b></label>
                    <select name="id_kegiatan" class="form-select">
                        <option value="">Semua</option>
                        <?php while($kg = $kegiatanRes->fetch_assoc()): ?>
                            <option value="<?= $kg['id'] ?>"
                                <?= $filter_kegiatan == $kg['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kg['nama_kegiatan']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="lihat_jurnal.php?id_siswa=<?= $id_siswa ?>"
                       class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL JURNAL -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th>Tanggal</th>
                <th>Kegiatan</th>
                <th>Detail</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>

            <?php if ($jurnal->num_rows == 0): ?>
                <tr>
                    <td colspan="4" class="text-center text-danger">Tidak ada jurnal.</td>
                </tr>
            <?php endif; ?>

            <?php while($d = $jurnal->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($d['tanggal']) ?></td>
                    <td><?= htmlspecialchars($d['nama_kegiatan']) ?></td>
                    <td>
                        <?php
                        if ($d['jam_bangun'])     echo "Bangun: {$d['jam_bangun']}<br>";
                        if ($d['ibadah'])         echo "Ibadah: {$d['ibadah']} ({$d['jam_ibadah']})<br>";
                        if ($d['olahraga'])       echo "Olahraga: {$d['olahraga']}<br>";
                        if ($d['belajar'])        echo "Belajar: {$d['belajar']}<br>";
                        if ($d['makanan_sehat'])  echo "Makan Sehat: {$d['makanan_sehat']}<br>";
                        if ($d['masyarakat'])     echo "Masyarakat: {$d['masyarakat']}<br>";
                        if ($d['jam_tidur'])      echo "Tidur: {$d['jam_tidur']}<br>";
                        if ($d['catatan'])        echo "<i>Catatan:</i> {$d['catatan']}";
                        ?>
                    </td>
                    <td>
                        <?php if ($d['nilai'] == 'Sudah'): ?>
                            <span class="badge bg-success">Sudah</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Belum</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>
    </div>

</div>

<?php include 'layout/footer.php'; ?>

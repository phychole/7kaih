<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') exit;

include 'layout/header.php';
include 'layout/sidebar.php';

$id_guru  = (int)($_SESSION['user_id'] ?? 0);
$id_siswa = (int)($_GET['id_siswa'] ?? 0);

if ($id_siswa <= 0) exit("ID siswa tidak valid.");

// =====================
// FILTER (RANGE TANGGAL)
// =====================
$tgl_awal  = $_GET['tanggal_awal'] ?? '';
$tgl_akhir = $_GET['tanggal_akhir'] ?? '';
$filter_kegiatan = $_GET['kegiatan'] ?? '';

function validDate($d) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
}

// Normalisasi range
if ($tgl_awal !== '' && $tgl_akhir === '') $tgl_akhir = $tgl_awal;
if ($tgl_akhir !== '' && $tgl_awal === '') $tgl_awal = $tgl_akhir;

if ($tgl_awal !== '' && !validDate($tgl_awal))  exit("Format tanggal_awal tidak valid.");
if ($tgl_akhir !== '' && !validDate($tgl_akhir)) exit("Format tanggal_akhir tidak valid.");

$kegiatan_id = ($filter_kegiatan !== '') ? (int)$filter_kegiatan : 0;

// =====================
// CEK AKSES: guru wali / wali kelas
// =====================
$cek = $conn->prepare("
    SELECT s.id
    FROM siswa s
    LEFT JOIN kelas k ON s.id_kelas = k.id
    LEFT JOIN guru_wali_siswa gw ON gw.id_siswa = s.id
    WHERE s.id = ?
      AND (gw.id_guru = ? OR k.id_guru_wali = ?)
    LIMIT 1
");
$cek->bind_param("iii", $id_siswa, $id_guru, $id_guru);
$cek->execute();
$cekRes = $cek->get_result()->fetch_assoc();
if (!$cekRes) exit("Anda tidak punya akses jurnal siswa ini.");

// =====================
// INFO SISWA
// =====================
$stmt = $conn->prepare("
    SELECT s.nama, s.nisn, k.nama_kelas
    FROM siswa s
    JOIN kelas k ON s.id_kelas = k.id
    WHERE s.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
if (!$s) exit("Data siswa tidak ditemukan.");

// =====================
// LIST KEGIATAN
// =====================
$list_kegiatan = $conn->query("SELECT * FROM jenis_kegiatan ORDER BY nama_kegiatan ASC");

// =====================
// QUERY JURNAL (prepared)
// =====================
$sql = "
    SELECT j.*, jk.nama_kegiatan
    FROM jurnal_siswa j
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    WHERE j.id_siswa = ?
";
$types = "i";
$params = [$id_siswa];

if ($tgl_awal !== '' && $tgl_akhir !== '') {
    $sql .= " AND j.tanggal BETWEEN ? AND ? ";
    $types .= "ss";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
}
if ($kegiatan_id > 0) {
    $sql .= " AND j.id_kegiatan = ? ";
    $types .= "i";
    $params[] = $kegiatan_id;
}

$sql .= " ORDER BY j.tanggal DESC, j.id DESC";

$q = $conn->prepare($sql);
if (!$q) exit("Query gagal: " . $conn->error);

$q->bind_param($types, ...$params);
$q->execute();
$jurnal = $q->get_result();

// =====================
// BACK URL (aman, same-host)
// =====================
$defaultBack = 'index.php';
$back = $defaultBack;

if (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    $hostOk = (parse_url($ref, PHP_URL_HOST) === $_SERVER['HTTP_HOST']);
    if ($hostOk) {
        $path  = parse_url($ref, PHP_URL_PATH) ?? '';
        $query = parse_url($ref, PHP_URL_QUERY);
        $back  = ltrim($path, '/');
        if ($query) $back .= '?' . $query;

        if (strpos($back, 'admin/') === false) $back = $defaultBack;
    }
}

// =====================
// EXPORT URL (ikut filter)
// =====================
$exportUrl = "export_jurnal.php?id_siswa=" . urlencode($id_siswa)
    . "&tanggal_awal=" . urlencode($tgl_awal)
    . "&tanggal_akhir=" . urlencode($tgl_akhir)
    . "&kegiatan=" . urlencode($kegiatan_id);
?>

<div class="content">

    <h3>
        <i class="fas fa-book-reader"></i> Jurnal Siswa: <?= htmlspecialchars($s['nama']) ?>
    </h3>

    <p class="text-muted">
        NISN: <b><?= htmlspecialchars($s['nisn']) ?></b> —
        Kelas: <b><?= htmlspecialchars($s['nama_kelas']) ?></b>
    </p>

    <div class="mb-3">
        <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="<?= htmlspecialchars($back) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">

                <div class="col-md-3">
                    <label><b>Tanggal Awal</b></label>
                    <input type="date" name="tanggal_awal" class="form-control"
                           value="<?= htmlspecialchars($tgl_awal) ?>">
                </div>

                <div class="col-md-3">
                    <label><b>Tanggal Akhir</b></label>
                    <input type="date" name="tanggal_akhir" class="form-control"
                           value="<?= htmlspecialchars($tgl_akhir) ?>">
                </div>

                <div class="col-md-4">
                    <label><b>Kegiatan</b></label>
                    <select name="kegiatan" class="form-select">
                        <option value="">Semua</option>
                        <?php while($kg = $list_kegiatan->fetch_assoc()): ?>
                            <option value="<?= (int)$kg['id'] ?>"
                                <?= ($kegiatan_id == (int)$kg['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kg['nama_kegiatan']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary me-2" type="submit">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="lihat_jurnal.php?id_siswa=<?= $id_siswa ?>" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
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
                    if (!empty($d['jam_bangun_pagi'])) echo "<b>Bangun Pagi:</b> " . htmlspecialchars($d['jam_bangun_pagi']) . "<br>";
                    if (!empty($d['jam_bangun']))      echo "<b>Bangun:</b> " . htmlspecialchars($d['jam_bangun']) . "<br>";
                    if (!empty($d['ibadah']))          echo "<b>Ibadah:</b> " . htmlspecialchars($d['ibadah']) .
                                                        (!empty($d['jam_ibadah']) ? " (" . htmlspecialchars($d['jam_ibadah']) . ")" : "") . "<br>";
                    if (!empty($d['olahraga']))        echo "<b>Olahraga:</b> " . htmlspecialchars($d['olahraga']) . "<br>";
                    if (!empty($d['belajar']))         echo "<b>Belajar:</b> " . htmlspecialchars($d['belajar']) . "<br>";
                    if (!empty($d['waktu_makan']))     echo "<b>Waktu Makan:</b> " . htmlspecialchars($d['waktu_makan']) . "<br>";
                    if (!empty($d['makanan_sehat']))   echo "<b>Makanan Sehat:</b> " . nl2br(htmlspecialchars($d['makanan_sehat'])) . "<br>";
                    if (!empty($d['masyarakat']))      echo "<b>Bermasyarakat:</b> " . htmlspecialchars($d['masyarakat']) . "<br>";
                    if (!empty($d['jam_tidur']))       echo "<b>Tidur:</b> " . htmlspecialchars($d['jam_tidur']) . "<br>";
                    if (!empty($d['catatan']))         echo "<i>Catatan:</i> " . nl2br(htmlspecialchars($d['catatan']));
                    ?>
                </td>
                <td>
                    <?php if (($d['nilai'] ?? '') === 'Sudah'): ?>
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

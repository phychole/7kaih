<?php
include 'layout/header.php';
include 'layout/sidebar.php';

// ------------------------------
// Ambil daftar kelas
// ------------------------------
$kelasRes = $conn->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$id_kelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : 0;

$kelasOptions = [];
$namaKelas = "Semua Kelas";

while ($k = $kelasRes->fetch_assoc()) {
    $kelasOptions[] = $k;
    if ($k['id'] == $id_kelas) {
        $namaKelas = $k['nama_kelas'];
    }
}

// ------------------------------
// Filter kelas untuk query
// ------------------------------
$kelasFilter = "";
if ($id_kelas > 0) {
    $kelasFilter = " AND s.id_kelas = " . $id_kelas . " ";
}

// ------------------------------
// Kard Statistik
// ------------------------------

// total jurnal
$rowJ = $conn->query("
    SELECT COUNT(*) AS total 
    FROM jurnal_siswa j
    JOIN siswa s ON j.id_siswa = s.id
    WHERE 1=1 $kelasFilter
")->fetch_assoc();
$totalJurnal = (int)($rowJ['total'] ?? 0);

// total siswa
if ($id_kelas > 0) {
    $rowS = $conn->query("SELECT COUNT(*) AS total FROM siswa WHERE id_kelas = $id_kelas")->fetch_assoc();
} else {
    $rowS = $conn->query("SELECT COUNT(*) AS total FROM siswa")->fetch_assoc();
}
$totalSiswa = (int)($rowS['total'] ?? 0);

// rata-rata jurnal/siswa
$rataJurnal = ($totalSiswa > 0) ? round($totalJurnal / $totalSiswa, 1) : 0;

// ------------------------------
// Data grafik: jurnal per kegiatan
// ------------------------------
$kegLabels = [];
$kegValues = [];
$qKeg = $conn->query("
    SELECT jk.nama_kegiatan, COUNT(j.id) AS total
    FROM jurnal_siswa j
    JOIN siswa s ON j.id_siswa = s.id
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    WHERE 1=1 $kelasFilter
    GROUP BY jk.id
    ORDER BY jk.id
");
while ($r = $qKeg->fetch_assoc()) {
    $kegLabels[] = $r['nama_kegiatan'];
    $kegValues[] = (int)$r['total'];
}

// ------------------------------
// Data grafik: 7 hari terakhir
// ------------------------------
$tglLabels = [];
$tglValues = [];
$qTgl = $conn->query("
    SELECT j.tanggal, COUNT(*) AS total
    FROM jurnal_siswa j
    JOIN siswa s ON j.id_siswa = s.id
    WHERE 1=1 $kelasFilter
      AND j.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY j.tanggal
    ORDER BY j.tanggal
");
while ($r = $qTgl->fetch_assoc()) {
    $tglLabels[] = $r['tanggal'];
    $tglValues[] = (int)$r['total'];
}

// ------------------------------
// Ranking siswa (Top 10) teraktif
// ------------------------------
$rankLabels = [];
$rankValues = [];
$qRank = $conn->query("
    SELECT s.nama, COUNT(j.id) AS total
    FROM jurnal_siswa j
    JOIN siswa s ON j.id_siswa = s.id
    WHERE 1=1 $kelasFilter
    GROUP BY j.id_siswa
    ORDER BY total DESC
    LIMIT 10
");
while ($r = $qRank->fetch_assoc()) {
    $rankLabels[] = $r['nama'];
    $rankValues[] = (int)$r['total'];
}
?>

<div class="content">

    <h2 class="mb-2"><i class="fas fa-gauge-high"></i> Dashboard Kurikulum</h2>
    <p class="text-muted mb-4">
        Statistik jurnal 7 kebiasaan untuk: <strong><?= htmlspecialchars($namaKelas) ?></strong>
    </p>

    <!-- FILTER KELAS -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label"><b>Pilih Kelas</b></label>
            <select name="id_kelas" class="form-select">
                <option value="0">Semua Kelas</option>
                <?php foreach($kelasOptions as $k): ?>
                    <option value="<?= $k['id'] ?>"
                        <?= $id_kelas == $k['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">
                <i class="fas fa-filter"></i> Tampilkan
            </button>
        </div>
    </form>

    <!-- KARTU STATISTIK -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-kur shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Jurnal</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 me-3"><?= $totalJurnal ?></span>
                        <i class="fas fa-book-open fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-kur shadow-sm border-0">
                <div class="card-body">
                    <h6>Jumlah Siswa</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 me-3"><?= $totalSiswa ?></span>
                        <i class="fas fa-user-graduate fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-kur shadow-sm border-0">
                <div class="card-body">
                    <h6>Rata-rata Jurnal / Siswa</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 me-3"><?= $rataJurnal ?></span>
                        <i class="fas fa-chart-line fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAFIK -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    Jurnal per Kegiatan
                </div>
                <div class="card-body">
                    <canvas id="chartKegiatan"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    Jurnal 7 Hari Terakhir
                </div>
                <div class="card-body">
                    <canvas id="chartTanggal"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- RANKING -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    Top 10 Siswa Teraktif
                </div>
                <div class="card-body">
                    <canvas id="chartRanking"></canvas>
                </div>
            </div>
        </div>

        <!-- Bisa gunakan bar lagi untuk kegiatan -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    Kegiatan Paling Sering Diisi
                </div>
                <div class="card-body">
                    <canvas id="chartKegiatanBar"></canvas>
                </div>
            </div>
        </div>
    </div>

</div> <!-- end content -->

<script>
// Data dari PHP
const kegLabels   = <?= json_encode($kegLabels) ?>;
const kegValues   = <?= json_encode($kegValues) ?>;
const tglLabels   = <?= json_encode($tglLabels) ?>;
const tglValues   = <?= json_encode($tglValues) ?>;
const rankLabels  = <?= json_encode($rankLabels) ?>;
const rankValues  = <?= json_encode($rankValues) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Pie chart per kegiatan
    if (document.getElementById('chartKegiatan')) {
        new Chart(document.getElementById('chartKegiatan'), {
            type: 'pie',
            data: {
                labels: kegLabels,
                datasets: [{
                    data: kegValues
                }]
            }
        });
    }

    // Line chart per tanggal
    if (document.getElementById('chartTanggal')) {
        new Chart(document.getElementById('chartTanggal'), {
            type: 'line',
            data: {
                labels: tglLabels,
                datasets: [{
                    label: 'Jumlah Jurnal',
                    data: tglValues,
                    fill: false,
                    tension: 0.3
                }]
            }
        });
    }

    // Bar chart ranking siswa
    if (document.getElementById('chartRanking')) {
        new Chart(document.getElementById('chartRanking'), {
            type: 'bar',
            data: {
                labels: rankLabels,
                datasets: [{
                    label: 'Jumlah Jurnal',
                    data: rankValues
                }]
            },
            options: {
                indexAxis: 'y'
            }
        });
    }

    // Bar chart kegiatan (sama data dengan pie, tapi versi bar)
    if (document.getElementById('chartKegiatanBar')) {
        new Chart(document.getElementById('chartKegiatanBar'), {
            type: 'bar',
            data: {
                labels: kegLabels,
                datasets: [{
                    label: 'Jumlah Jurnal',
                    data: kegValues
                }]
            }
        });
    }
});
</script>

<?php include 'layout/footer.php'; ?>

<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$id_siswa = (int)($_SESSION['user_id'] ?? 0);
$nama_siswa = $_SESSION['nama'] ?? '';
$today = date('Y-m-d');

// Ambil data siswa (untuk agama)
$stS = $conn->prepare("SELECT agama FROM siswa WHERE id = ? LIMIT 1");
$stS->bind_param("i", $id_siswa);
$stS->execute();
$siswa = $stS->get_result()->fetch_assoc();
$agama = strtolower(trim($siswa['agama'] ?? ''));

// Ambil jenis_kegiatan
$kegiatan = [];
$res = $conn->query("SELECT * FROM jenis_kegiatan ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $kegiatan[] = $row;

// Mapping icon (fallback star)
$icons = [
    1 => 'sunrise',
    2 => 'heart',
    3 => 'bicycle',
    4 => 'cup-hot',
    5 => 'book',
    6 => 'people',
    7 => 'moon'
];

// ===== Hitung total jurnal hari ini + status per kegiatan =====

// total entri jurnal hari ini
$stTotal = $conn->prepare("SELECT COUNT(*) AS c FROM jurnal_siswa WHERE id_siswa = ? AND tanggal = ?");
$stTotal->bind_param("is", $id_siswa, $today);
$stTotal->execute();
$totalHariIni = (int)($stTotal->get_result()->fetch_assoc()['c'] ?? 0);

// counts umum per id_kegiatan hari ini
$counts = []; // [id_kegiatan => jumlah entri]
$stCounts = $conn->prepare("
    SELECT id_kegiatan, COUNT(*) AS c
    FROM jurnal_siswa
    WHERE id_siswa = ? AND tanggal = ?
    GROUP BY id_kegiatan
");
$stCounts->bind_param("is", $id_siswa, $today);
$stCounts->execute();
$r = $stCounts->get_result();
while ($x = $r->fetch_assoc()) {
    $counts[(int)$x['id_kegiatan']] = (int)$x['c'];
}

// auto-detect id_kegiatan untuk IBADAH & MAKAN berdasarkan nama_kegiatan
$idIbadah = 0;
$idMakan  = 0;
foreach ($kegiatan as $k) {
    $nama = strtolower(trim($k['nama_kegiatan']));
    if ($idIbadah == 0 && strpos($nama, 'ibadah') !== false) $idIbadah = (int)$k['id'];
    if ($idMakan == 0 && (strpos($nama, 'makan') !== false)) $idMakan = (int)$k['id'];
}

// detail ibadah hari ini (untuk Islam: cek 5 waktu)
$ibadahSet = [];
if ($idIbadah > 0) {
    $stIbd = $conn->prepare("
        SELECT ibadah
        FROM jurnal_siswa
        WHERE id_siswa = ? AND tanggal = ? AND id_kegiatan = ?
          AND ibadah IS NOT NULL AND ibadah <> ''
    ");
    $stIbd->bind_param("isi", $id_siswa, $today, $idIbadah);
    $stIbd->execute();
    $ri = $stIbd->get_result();
    while ($row = $ri->fetch_assoc()) {
        $val = strtolower(trim($row['ibadah']));
        if ($val !== '') $ibadahSet[$val] = true;
    }
}

// detail makan hari ini: distinct waktu_makan (Pagi/Siang/Malam)
$makanSet = [];
if ($idMakan > 0) {
    $stMk = $conn->prepare("
        SELECT waktu_makan
        FROM jurnal_siswa
        WHERE id_siswa = ? AND tanggal = ? AND id_kegiatan = ?
          AND waktu_makan IS NOT NULL AND waktu_makan <> ''
    ");
    $stMk->bind_param("isi", $id_siswa, $today, $idMakan);
    $stMk->execute();
    $rm = $stMk->get_result();
    while ($row = $rm->fetch_assoc()) {
        $val = strtolower(trim($row['waktu_makan'])); // pagi/siang/malam
        if ($val !== '') $makanSet[$val] = true;
    }
}

// helper: status badge/icon
// return one of: success (hijau), warning (kuning), danger (merah)
function statusKegiatan($id, $counts, $idIbadah, $idMakan, $agama, $ibadahSet, $makanSet) {
    $jml = $counts[$id] ?? 0;

    // belum isi sama sekali
    if ($jml <= 0) return 'danger';

    // aturan khusus ibadah
    if ($idIbadah > 0 && $id == $idIbadah) {
        if ($agama === 'islam') {
            $wajib = ['subuh','dzuhur','zuhur','ashar','asar','magrib','isya'];
            // Normalisasi: dzuhur vs zuhur, ashar vs asar
            $need = ['subuh'=>false,'dzuhur'=>false,'ashar'=>false,'magrib'=>false,'isya'=>false];

            foreach ($ibadahSet as $k => $_) {
                if ($k === 'subuh') $need['subuh'] = true;
                if ($k === 'dzuhur' || $k === 'zuhur') $need['dzuhur'] = true;
                if ($k === 'ashar' || $k === 'asar') $need['ashar'] = true;
                if ($k === 'magrib') $need['magrib'] = true;
                if ($k === 'isya') $need['isya'] = true;
            }
            $done = 0;
            foreach ($need as $v) if ($v) $done++;

            if ($done >= 5) return 'success';
            return 'warning'; // ada, tapi belum lengkap
        } else {
            // non-islam minimal 1
            return ($jml >= 1) ? 'success' : 'danger';
        }
    }

    // aturan khusus makan: harus pagi+siang+malam
    if ($idMakan > 0 && $id == $idMakan) {
        $need = ['pagi'=>false,'siang'=>false,'malam'=>false];
        foreach ($makanSet as $k => $_) {
            if (isset($need[$k])) $need[$k] = true;
        }
        $done = 0;
        foreach ($need as $v) if ($v) $done++;
        if ($done >= 3) return 'success';
        return 'warning';
    }

    // default: minimal 1 entri => hijau
    return 'success';
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard Siswa</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body { background: #f7f8fa; }
    .activity-card { border-radius: 14px; transition: .25s; padding: 25px 10px; }
    .activity-card:hover { transform: translateY(-5px); box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
    .activity-icon { font-size: 2.8rem !important; }
    .col-6 { padding: 8px; }
    .card-body p { font-size: 0.95rem; font-weight: 600; }
    @media(max-width: 576px){
        .activity-icon { font-size: 3rem !important; }
        .card-body p { font-size: 1rem; }
    }
</style>

</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Siswa - Jurnal 7 Kebiasaan</span>
    <span class="navbar-text text-white me-3"><?= htmlspecialchars($nama_siswa) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container mt-4">

  <h4 class="text-center mb-1 fw-bold">Halo, <?= htmlspecialchars($nama_siswa); ?> 👋</h4>

  <!-- Notifikasi total jurnal hari ini -->
  <div class="alert alert-<?php echo ($totalHariIni > 0 ? 'success' : 'danger'); ?> text-center">
      Hari ini kamu sudah mengisi <b><?= (int)$totalHariIni ?></b> jurnal.
      <?php if ($agama === 'islam'): ?>
          <div class="small mt-1 text-muted">Target Ibadah: Subuh, Dzuhur, Ashar, Magrib, Isya • Target Makan: Pagi, Siang, Malam</div>
      <?php else: ?>
          <div class="small mt-1 text-muted">Target Ibadah: minimal 1 • Target Makan: Pagi, Siang, Malam</div>
      <?php endif; ?>
  </div>

  <p class="text-center text-muted mb-4">Pilih kegiatan yang kamu lakukan hari ini</p>

  <div class="row justify-content-center">
    <?php foreach ($kegiatan as $k): ?>
        <?php
        $kid = (int)$k['id'];
        $icon = $icons[$kid] ?? 'star';

        $st = statusKegiatan($kid, $counts, $idIbadah, $idMakan, $agama, $ibadahSet, $makanSet);
        // icon color
        $iconClass = "text-$st";

        // label kecil status
        $label = '';
        if ($st === 'danger') $label = 'Belum';
        if ($st === 'success') $label = 'Lengkap';
        if ($st === 'warning') $label = 'Kurang';

        // sub status untuk ibadah & makan
        $sub = '';
        if ($kid === $idIbadah) {
            if ($agama === 'islam') {
                $done = 0;
                $need = ['subuh'=>false,'dzuhur'=>false,'ashar'=>false,'magrib'=>false,'isya'=>false];
                foreach ($ibadahSet as $v => $_) {
                    if ($v === 'subuh') $need['subuh'] = true;
                    if ($v === 'dzuhur' || $v === 'zuhur') $need['dzuhur'] = true;
                    if ($v === 'ashar' || $v === 'asar') $need['ashar'] = true;
                    if ($v === 'magrib') $need['magrib'] = true;
                    if ($v === 'isya') $need['isya'] = true;
                }
                foreach ($need as $v) if ($v) $done++;
                $sub = "$done/5";
            } else {
                $sub = (($counts[$kid] ?? 0) > 0) ? "1/1" : "0/1";
            }
        } elseif ($kid === $idMakan) {
            $done = 0;
            foreach (['pagi','siang','malam'] as $w) if (!empty($makanSet[$w])) $done++;
            $sub = "$done/3";
        } else {
            $sub = (($counts[$kid] ?? 0) > 0) ? (($counts[$kid] ?? 0) . " entri") : "0 entri";
        }
        ?>

        <div class="col-6 col-md-3 mb-3">
            <a href="isi_jurnal.php?id_kegiatan=<?= $kid ?>" class="text-decoration-none">
                <div class="card shadow-sm activity-card text-center h-100">
                    <i class="bi bi-<?= htmlspecialchars($icon) ?> <?= $iconClass ?> activity-icon"></i>
                    <div class="card-body">
                        <p class="mb-1"><?= htmlspecialchars($k['nama_kegiatan']); ?></p>
                        <span class="badge bg-<?= $st ?>"><?= htmlspecialchars($label) ?> • <?= htmlspecialchars($sub) ?></span>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-3">
    <a href="riwayat.php" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-clock-history"></i> Lihat Riwayat Jurnal
    </a> <a href="rekap_harian_fullbulan.php" class="btn btn-outline-primary btn-sm">
  Rekap Harian Bulanan
</a>
  </div>

</div>

</body>
</html>

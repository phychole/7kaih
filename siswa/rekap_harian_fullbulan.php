<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$id_siswa = (int)($_SESSION['user_id'] ?? 0);

// filter bulan & tahun
$year  = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) $month = (int)date('n');

$start = sprintf("%04d-%02d-01", $year, $month);
$end   = date("Y-m-t", strtotime($start)); // last day of month

$bulanNama = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$judulBulan = ($bulanNama[$month] ?? '') . " " . $year;

// =====================
// IDENTITAS SISWA
// =====================
$info = $conn->prepare("
  SELECT s.nama, s.nisn, s.agama, k.nama_kelas
  FROM siswa s
  JOIN kelas k ON s.id_kelas = k.id
  WHERE s.id = ?
  LIMIT 1
");
$info->bind_param("i", $id_siswa);
$info->execute();
$siswa = $info->get_result()->fetch_assoc();
if (!$siswa) exit("Data siswa tidak ditemukan.");

$agamaLower = strtolower(trim($siswa['agama'] ?? ''));

// =====================
// AMBIL REKAP PER HARI (HANYA TANGGAL YANG ADA DATA)
// nanti kita "expand" jadi full 1 bulan di PHP
// =====================
$sql = "
SELECT
  tgl,

  MAX(id_kegiatan=1) AS bangun,
  MAX(id_kegiatan=3) AS olahraga,
  MAX(id_kegiatan=5) AS belajar,
  MAX(id_kegiatan=6) AS masyarakat,
  MAX(id_kegiatan=7) AS tidur,

  MAX(id_kegiatan=2) AS ibadah_any,
  COUNT(DISTINCT CASE
    WHEN id_kegiatan=2 AND ibadah IS NOT NULL AND ibadah<>'' THEN
      CASE
        WHEN LOWER(ibadah)='zuhur' THEN 'dzuhur'
        WHEN LOWER(ibadah)='asar'  THEN 'ashar'
        ELSE LOWER(ibadah)
      END
  END) AS ibadah_cnt,

  MAX(id_kegiatan=4) AS makan_any,
  COUNT(DISTINCT CASE
    WHEN id_kegiatan=4 AND waktu_makan IS NOT NULL AND waktu_makan<>'' THEN LOWER(waktu_makan)
  END) AS makan_cnt

FROM (
  SELECT
    DATE(j.tanggal) AS tgl,
    j.id_kegiatan,
    j.ibadah,
    j.waktu_makan
  FROM jurnal_siswa j
  WHERE j.id_siswa = ?
    AND DATE(j.tanggal) BETWEEN ? AND ?
) x
GROUP BY tgl
";
$q = $conn->prepare($sql);
$q->bind_param("iss", $id_siswa, $start, $end);
$q->execute();
$res = $q->get_result();

// simpan ke map: dataByDate['YYYY-MM-DD'] = row
$dataByDate = [];
while($r = $res->fetch_assoc()){
    $dataByDate[$r['tgl']] = $r;
}

// helper icon
function iconTF($v){
    return ((int)$v === 1)
        ? '<span class="text-success fw-bold">✓</span>'
        : '<span class="text-danger fw-bold">✗</span>';
}

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rekap Harian Full Bulan</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{ background:#f4f6f9; }
  .container{ max-width: 1100px; }
  .badge-mini{ font-size: 12px; }
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Rekap Harian (Full 1 Bulan)</span>
    <a href="index.php" class="btn btn-outline-light btn-sm">Kembali</a>
  </div>
</nav>

<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <h5 class="fw-bold mb-1">Identitas Siswa</h5>
      <div class="text-muted">
        Nama: <b><?= htmlspecialchars($siswa['nama']) ?></b> |
        NISN: <b><?= htmlspecialchars($siswa['nisn']) ?></b> |
        Agama: <b><?= htmlspecialchars($siswa['agama']) ?></b> |
        Kelas: <b><?= htmlspecialchars($siswa['nama_kelas']) ?></b>
      </div>
      <div class="mt-1">
        Periode: <b><?= htmlspecialchars($judulBulan) ?></b>
      </div>
    </div>

    <div class="text-end">
      <a target="_blank"
         class="btn btn-danger"
         href="rekap_harian_fullbulan_pdf.php?year=<?= $year ?>&month=<?= $month ?>">
        Export PDF
      </a>
    </div>
  </div>

  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="GET">
        <div class="col-6 col-md-2">
          <label class="form-label"><b>Tahun</b></label>
          <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2020" max="2100">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label"><b>Bulan</b></label>
          <select name="month" class="form-select">
            <?php foreach($bulanNama as $i=>$nm): ?>
              <option value="<?= $i ?>" <?= ($i==$month?'selected':'') ?>><?= $nm ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-7 text-end">
          <button class="btn btn-primary">Tampilkan</button>
          <a href="rekap_harian_fullbulan.php" class="btn btn-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive mt-3">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary text-center">
        <tr>
          <th style="width:120px;">Tanggal</th>
          <th>Bangun Pagi</th>
          <th>Ibadah<br><small>(jumlah)</small></th>
          <th>Olahraga</th>
          <th>Makan Bergizi<br><small>(jumlah)</small></th>
          <th>Gemar Belajar</th>
          <th>Bermasyarakat</th>
          <th>Tidur Cepat</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // loop semua tanggal dalam 1 bulan
      $dtStart = new DateTime($start);
      $dtEnd   = new DateTime($end);
      $dtEnd->modify('+1 day'); // biar include end

      $period = new DatePeriod($dtStart, new DateInterval('P1D'), $dtEnd);

      foreach($period as $d){
        $tgl = $d->format('Y-m-d');

        // default kosong
        $row = $dataByDate[$tgl] ?? [
            'bangun'=>0,'ibadah_any'=>0,'olahraga'=>0,'makan_any'=>0,'belajar'=>0,'masyarakat'=>0,'tidur'=>0,
            'ibadah_cnt'=>0,'makan_cnt'=>0
        ];

        $ibc = (int)($row['ibadah_cnt'] ?? 0);
        $mkc = (int)($row['makan_cnt'] ?? 0);

        $ibText = ($agamaLower === 'islam') ? ($ibc . "/5") : (string)$ibc;
        $mkText = $mkc . "/3";
      ?>
        <tr class="text-center">
          <td><?= htmlspecialchars($tgl) ?></td>

          <td><?= iconTF($row['bangun']) ?></td>

          <td>
            <?= iconTF($row['ibadah_any']) ?>
            <div><span class="badge bg-secondary badge-mini"><?= htmlspecialchars($ibText) ?></span></div>
          </td>

          <td><?= iconTF($row['olahraga']) ?></td>

          <td>
            <?= iconTF($row['makan_any']) ?>
            <div><span class="badge bg-secondary badge-mini"><?= htmlspecialchars($mkText) ?></span></div>
          </td>

          <td><?= iconTF($row['belajar']) ?></td>
          <td><?= iconTF($row['masyarakat']) ?></td>
          <td><?= iconTF($row['tidur']) ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>

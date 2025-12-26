<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') exit;

date_default_timezone_set('Asia/Jakarta');

$id_siswa = (int)($_SESSION['user_id'] ?? 0);

$year  = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) $month = (int)date('n');

$start = sprintf("%04d-%02d-01", $year, $month);
$end   = date("Y-m-t", strtotime($start));

$bulanNama = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$judulBulan = ($bulanNama[$month] ?? '') . " " . $year;

// identitas
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

// ambil rekap yang ada datanya
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

$dataByDate = [];
while($r = $res->fetch_assoc()){
  $dataByDate[$r['tgl']] = $r;
}

function iconTF($v){ return ((int)$v===1) ? '✓' : '✗'; }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rekap Harian Full Bulan <?= htmlspecialchars($judulBulan) ?></title>
<style>
  body{ font-family: Arial, sans-serif; font-size: 12px; color:#111; }
  h2{ margin:0 0 6px 0; font-size: 16px; }
  .meta{ margin:0 0 10px 0; color:#333; }
  table{ width:100%; border-collapse: collapse; }
  th, td{ border:1px solid #333; padding:6px; }
  th{ background:#eee; text-align:center; }
  td{ text-align:center; }
  .ok{ color:#0a7a28; font-weight:700; }
  .no{ color:#b00020; font-weight:700; }
  .small{ font-size: 11px; color:#333; }
  @page{ size: A4 landscape; margin: 10mm; }
  @media print { .no-print{ display:none; } }
</style>
</head>
<body>

<h2>Rekap Harian Jurnal 7 Kebiasaan (Full 1 Bulan)</h2>
<div class="meta">
  Nama: <b><?= htmlspecialchars($siswa['nama']) ?></b> |
  NISN: <b><?= htmlspecialchars($siswa['nisn']) ?></b> |
  Agama: <b><?= htmlspecialchars($siswa['agama']) ?></b> |
  Kelas: <b><?= htmlspecialchars($siswa['nama_kelas']) ?></b><br>
  Periode: <b><?= htmlspecialchars($judulBulan) ?></b> |
  Dicetak: <?= date('d-m-Y H:i') ?>
</div>

<div class="no-print" style="margin-bottom:10px;">
  <button onclick="window.print()">Cetak / Save as PDF</button>
</div>

<table>
  <thead>
    <tr>
      <th>Tanggal</th>
      <th>Bangun</th>
      <th>Ibadah<br><span class="small">(jumlah)</span></th>
      <th>Olahraga</th>
      <th>Makan<br><span class="small">(jumlah)</span></th>
      <th>Belajar</th>
      <th>Masyarakat</th>
      <th>Tidur</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $dtStart = new DateTime($start);
    $dtEnd   = new DateTime($end);
    $dtEnd->modify('+1 day');

    $period = new DatePeriod($dtStart, new DateInterval('P1D'), $dtEnd);

    foreach($period as $d){
      $tgl = $d->format('Y-m-d');

      $row = $dataByDate[$tgl] ?? [
        'bangun'=>0,'ibadah_any'=>0,'olahraga'=>0,'makan_any'=>0,'belajar'=>0,'masyarakat'=>0,'tidur'=>0,
        'ibadah_cnt'=>0,'makan_cnt'=>0
      ];

      $ibc = (int)($row['ibadah_cnt'] ?? 0);
      $mkc = (int)($row['makan_cnt'] ?? 0);

      $ibText = ($agamaLower === 'islam') ? ($ibc . "/5") : (string)$ibc;
      $mkText = $mkc . "/3";
    ?>
      <tr>
        <td><?= htmlspecialchars($tgl) ?></td>

        <td class="<?= ((int)$row['bangun']===1?'ok':'no') ?>"><?= iconTF($row['bangun']) ?></td>

        <td class="<?= ((int)$row['ibadah_any']===1?'ok':'no') ?>">
          <?= iconTF($row['ibadah_any']) ?><br>
          <span class="small"><?= htmlspecialchars($ibText) ?></span>
        </td>

        <td class="<?= ((int)$row['olahraga']===1?'ok':'no') ?>"><?= iconTF($row['olahraga']) ?></td>

        <td class="<?= ((int)$row['makan_any']===1?'ok':'no') ?>">
          <?= iconTF($row['makan_any']) ?><br>
          <span class="small"><?= htmlspecialchars($mkText) ?></span>
        </td>

        <td class="<?= ((int)$row['belajar']===1?'ok':'no') ?>"><?= iconTF($row['belajar']) ?></td>
        <td class="<?= ((int)$row['masyarakat']===1?'ok':'no') ?>"><?= iconTF($row['masyarakat']) ?></td>
        <td class="<?= ((int)$row['tidur']===1?'ok':'no') ?>"><?= iconTF($row['tidur']) ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<script>
window.onload = () => window.print();
</script>
</body>
</html>

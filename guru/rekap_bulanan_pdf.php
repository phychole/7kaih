<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') exit;

date_default_timezone_set('Asia/Jakarta');

$id_guru = (int)($_SESSION['user_id'] ?? 0);
$year  = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) $month = (int)date('n');

// cek wali kelas
$stmt = $conn->prepare("SELECT id, nama_kelas FROM kelas WHERE id_guru_wali = ? LIMIT 1");
$stmt->bind_param("i", $id_guru);
$stmt->execute();
$kelas = $stmt->get_result()->fetch_assoc();
if (!$kelas) exit("Anda bukan wali kelas.");

$id_kelas = (int)$kelas['id'];

$start = sprintf("%04d-%02d-01", $year, $month);
$end   = date("Y-m-t", strtotime($start)); // last day of month

$bulanNama = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$judulBulan = ($bulanNama[$month] ?? '') . " $year";

/*
  Strategi query:
  1) buat ringkasan per siswa-per tanggal (day-level)
  2) akumulasi jadi bulanan: jumlah hari tiap kegiatan terisi, ibadah lengkap, makan lengkap
*/
$sql = "
SELECT
  s.id,
  s.nama,
  s.nisn,
  s.agama,

  COALESCE(SUM(d.bangun),0)     AS hari_bangun,
  COALESCE(SUM(d.ibadah_any),0) AS hari_ibadah_any,
  COALESCE(SUM(d.olahraga),0)   AS hari_olahraga,
  COALESCE(SUM(d.makan_any),0)  AS hari_makan_any,
  COALESCE(SUM(d.belajar),0)    AS hari_belajar,
  COALESCE(SUM(d.masyarakat),0) AS hari_masyarakat,
  COALESCE(SUM(d.tidur),0)      AS hari_tidur,

  COALESCE(SUM(
    CASE
      WHEN LOWER(TRIM(s.agama))='islam' THEN (d.ibadah_cnt >= 5)
      ELSE (d.ibadah_cnt >= 1)
    END
  ),0) AS hari_ibadah_lengkap,

  COALESCE(SUM(d.makan_cnt >= 3),0) AS hari_makan_lengkap

FROM siswa s
LEFT JOIN (
  SELECT
    j.id_siswa,
    j.tanggal,

    MAX(j.id_kegiatan=1) AS bangun,
    MAX(j.id_kegiatan=3) AS olahraga,
    MAX(j.id_kegiatan=5) AS belajar,
    MAX(j.id_kegiatan=6) AS masyarakat,
    MAX(j.id_kegiatan=7) AS tidur,

    MAX(j.id_kegiatan=2) AS ibadah_any,
    COUNT(DISTINCT CASE
      WHEN j.id_kegiatan=2 AND j.ibadah IS NOT NULL AND j.ibadah<>'' THEN
        CASE
          WHEN LOWER(j.ibadah)='zuhur' THEN 'dzuhur'
          WHEN LOWER(j.ibadah)='asar'  THEN 'ashar'
          ELSE LOWER(j.ibadah)
        END
    END) AS ibadah_cnt,

    MAX(j.id_kegiatan=4) AS makan_any,
    COUNT(DISTINCT CASE
      WHEN j.id_kegiatan=4 AND j.waktu_makan IS NOT NULL AND j.waktu_makan<>'' THEN LOWER(j.waktu_makan)
    END) AS makan_cnt

  FROM jurnal_siswa j
  WHERE j.tanggal BETWEEN ? AND ?
  GROUP BY j.id_siswa, j.tanggal
) d ON d.id_siswa = s.id
WHERE s.id_kelas = ?
GROUP BY s.id, s.nama, s.nisn, s.agama
ORDER BY s.nama ASC
";

$q = $conn->prepare($sql);
$q->bind_param("ssi", $start, $end, $id_kelas);
$q->execute();
$data = $q->get_result();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rekap Bulanan <?= htmlspecialchars($judulBulan) ?></title>
<style>
  body{ font-family: Arial, sans-serif; font-size: 12px; color:#111; }
  .header{ margin-bottom: 10px; }
  .header h2{ margin:0; font-size: 16px; }
  .meta{ margin-top:4px; color:#444; }
  table{ width:100%; border-collapse: collapse; }
  th, td{ border:1px solid #333; padding:6px; }
  th{ background:#eee; text-align:center; }
  td{ vertical-align: top; }
  .center{ text-align:center; }
  .small{ font-size: 11px; color:#333; }
  @page { size: A4 landscape; margin: 10mm; }
  @media print {
    .no-print{ display:none; }
  }
</style>
</head>
<body>

<div class="header">
  <h2>Rekap Bulanan Jurnal 7 Kebiasaan</h2>
  <div class="meta">
    Kelas: <b><?= htmlspecialchars($kelas['nama_kelas']) ?></b> |
    Periode: <b><?= htmlspecialchars($judulBulan) ?></b> |
    Dicetak: <?= date('d-m-Y H:i') ?>
  </div>
</div>

<div class="no-print" style="margin-bottom:10px;">
  <button onclick="window.print()">Cetak / Save as PDF</button>
</div>

<table>
  <thead>
    <tr>
      <th rowspan="2" style="width:35px;">No</th>
      <th rowspan="2">Nama</th>
      <th rowspan="2" style="width:95px;">NISN</th>
      <th rowspan="2" style="width:90px;">Agama</th>
      <th colspan="7">Jumlah Hari Terisi</th>
      <th rowspan="2" style="width:120px;">Ibadah<br><span class="small">lengkap / ada</span></th>
      <th rowspan="2" style="width:120px;">Makan<br><span class="small">lengkap / ada</span></th>
    </tr>
    <tr>
      <th>Bangun</th>
      <th>Ibadah</th>
      <th>Olahraga</th>
      <th>Makan</th>
      <th>Belajar</th>
      <th>Masyarakat</th>
      <th>Tidur</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($data->num_rows == 0): ?>
      <tr><td colspan="13" class="center">Tidak ada data.</td></tr>
    <?php endif; ?>

    <?php $no=1; while($s = $data->fetch_assoc()): 
      $agama = strtolower(trim($s['agama'] ?? ''));
      $ib_lengkap = (int)$s['hari_ibadah_lengkap'];
      $ib_ada     = (int)$s['hari_ibadah_any'];
      $mk_lengkap = (int)$s['hari_makan_lengkap'];
      $mk_ada     = (int)$s['hari_makan_any'];
    ?>
      <tr>
        <td class="center"><?= $no++ ?></td>
        <td><?= htmlspecialchars($s['nama']) ?></td>
        <td class="center"><?= htmlspecialchars($s['nisn']) ?></td>
        <td class="center"><?= htmlspecialchars($s['agama'] ?? '-') ?></td>

        <td class="center"><?= (int)$s['hari_bangun'] ?></td>
        <td class="center"><?= (int)$s['hari_ibadah_any'] ?></td>
        <td class="center"><?= (int)$s['hari_olahraga'] ?></td>
        <td class="center"><?= (int)$s['hari_makan_any'] ?></td>
        <td class="center"><?= (int)$s['hari_belajar'] ?></td>
        <td class="center"><?= (int)$s['hari_masyarakat'] ?></td>
        <td class="center"><?= (int)$s['hari_tidur'] ?></td>

        <td class="center">
          <?php if ($agama === 'islam'): ?>
            <?= $ib_lengkap ?> / <?= $ib_ada ?> <span class="small">(target 5 waktu/hari)</span>
          <?php else: ?>
            <?= $ib_lengkap ?> / <?= $ib_ada ?> <span class="small">(min 1/hari)</span>
          <?php endif; ?>
        </td>

        <td class="center">
          <?= $mk_lengkap ?> / <?= $mk_ada ?> <span class="small">(target 3x/hari)</span>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<script>
// auto open print dialog agar user langsung Save as PDF
window.onload = () => window.print();
</script>
</body>
</html>

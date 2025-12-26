<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') exit;

$id_guru  = (int)($_SESSION['user_id'] ?? 0);
$id_siswa = (int)($_GET['id_siswa'] ?? 0);

if ($id_siswa <= 0) exit("ID siswa tidak valid.");

// FILTER
$tgl_awal  = $_GET['tanggal_awal'] ?? '';
$tgl_akhir = $_GET['tanggal_akhir'] ?? '';
$kegiatan  = $_GET['kegiatan'] ?? '';

function validDate($d) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); }

if ($tgl_awal !== '' && $tgl_akhir === '') $tgl_akhir = $tgl_awal;
if ($tgl_akhir !== '' && $tgl_awal === '') $tgl_awal = $tgl_akhir;

if ($tgl_awal !== '' && !validDate($tgl_awal))  exit("Format tanggal_awal tidak valid.");
if ($tgl_akhir !== '' && !validDate($tgl_akhir)) exit("Format tanggal_akhir tidak valid.");

$kegiatan_id = ($kegiatan !== '') ? (int)$kegiatan : 0;

// CEK AKSES
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
if (!$cek->get_result()->fetch_assoc()) exit("Anda tidak memiliki akses.");

// INFO SISWA
$stS = $conn->prepare("SELECT nama FROM siswa WHERE id = ? LIMIT 1");
$stS->bind_param("i", $id_siswa);
$stS->execute();
$s = $stS->get_result()->fetch_assoc();
if (!$s) exit("Data siswa tidak ditemukan.");

$namaFile = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s['nama']);

// QUERY JURNAL
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

$stmt = $conn->prepare($sql);
if (!$stmt) exit("Query gagal: " . $conn->error);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$jurnal = $stmt->get_result();

// HEADER EXCEL
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Jurnal_{$namaFile}.xls");
header("Pragma: no-cache");
header("Expires: 0");

$judulRange = '';
if ($tgl_awal !== '' && $tgl_akhir !== '') $judulRange = " (Periode {$tgl_awal} s/d {$tgl_akhir})";
?>
<table border="1">
    <thead>
        <tr style="background:#cccccc;">
            <th colspan="4"><b>Rekap Jurnal - <?= htmlspecialchars($s['nama']) ?><?= htmlspecialchars($judulRange) ?></b></th>
        </tr>
        <tr style="background:#f0f0f0;">
            <th>Tanggal</th>
            <th>Kegiatan</th>
            <th>Detail</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($jurnal->num_rows == 0): ?>
        <tr><td colspan="4" style="color:red; text-align:center;">Tidak ada data jurnal.</td></tr>
    <?php endif; ?>

    <?php while($d = $jurnal->fetch_assoc()): ?>
        <?php
        $detail = [];
        if (!empty($d['jam_bangun_pagi'])) $detail[] = "Bangun Pagi: ".$d['jam_bangun_pagi'];
        if (!empty($d['jam_bangun']))      $detail[] = "Bangun: ".$d['jam_bangun'];
        if (!empty($d['ibadah']))          $detail[] = "Ibadah: ".$d['ibadah'].(!empty($d['jam_ibadah']) ? " (".$d['jam_ibadah'].")" : "");
        if (!empty($d['olahraga']))        $detail[] = "Olahraga: ".$d['olahraga'];
        if (!empty($d['belajar']))         $detail[] = "Belajar: ".$d['belajar'];
        if (!empty($d['waktu_makan']))     $detail[] = "Waktu Makan: ".$d['waktu_makan'];
        if (!empty($d['makanan_sehat']))   $detail[] = "Makanan Sehat: ".$d['makanan_sehat'];
        if (!empty($d['masyarakat']))      $detail[] = "Bermasyarakat: ".$d['masyarakat'];
        if (!empty($d['jam_tidur']))       $detail[] = "Tidur: ".$d['jam_tidur'];
        if (!empty($d['catatan']))         $detail[] = "Catatan: ".$d['catatan'];

        // Excel HTML: pakai <br> biar rapi
        $detailHtml = '';
        foreach ($detail as $line) $detailHtml .= htmlspecialchars($line) . "<br>";
        ?>
        <tr>
            <td><?= htmlspecialchars($d['tanggal']) ?></td>
            <td><?= htmlspecialchars($d['nama_kegiatan']) ?></td>
            <td><?= $detailHtml ?></td>
            <td><?= htmlspecialchars($d['nilai'] ?? '') ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

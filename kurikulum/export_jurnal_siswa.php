<?php
require '../config.php';
require '../auth.php';

$id_siswa = (int)($_GET['id_siswa'] ?? 0);
if ($id_siswa <= 0) exit("Invalid.");

$filter_tanggal  = $_GET['tanggal'] ?? '';
$filter_kegiatan = $_GET['id_kegiatan'] ?? '';

$s = $conn->query("
    SELECT s.*, k.nama_kelas 
    FROM siswa s 
    JOIN kelas k ON s.id_kelas = k.id
    WHERE s.id = $id_siswa
")->fetch_assoc();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=jurnal_".$s['nama'].".xls");
header("Pragma: no-cache");
header("Expires: 0");

// query jurnal
$sql = "
    SELECT j.*, jk.nama_kegiatan 
    FROM jurnal_siswa j
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    WHERE j.id_siswa = $id_siswa
";
if ($filter_tanggal != '') {
    $tgl = $conn->real_escape_string($filter_tanggal);
    $sql .= " AND j.tanggal = '$tgl' ";
}
if ($filter_kegiatan != '') {
    $sql .= " AND j.id_kegiatan = ".intval($filter_kegiatan);
}
$sql .= " ORDER BY j.tanggal DESC";

$j = $conn->query($sql);

echo "<h3>Jurnal Siswa: {$s['nama']} - {$s['nama_kelas']}</h3>";
echo "<table border='1' cellpadding='5'>
        <tr style='background:#ccc'>
            <th>Tanggal</th>
            <th>Kegiatan</th>
            <th>Detail</th>
            <th>Status</th>
        </tr>";

while($d = $j->fetch_assoc()) {

    $detail = "";
    if ($d['jam_bangun'])     $detail .= "Bangun: {$d['jam_bangun']}<br>";
    if ($d['ibadah'])         $detail .= "Ibadah: {$d['ibadah']} ({$d['jam_ibadah']})<br>";
    if ($d['olahraga'])       $detail .= "Olahraga: {$d['olahraga']}<br>";
    if ($d['belajar'])        $detail .= "Belajar: {$d['belajar']}<br>";
    if ($d['makanan_sehat'])  $detail .= "Makanan Sehat: {$d['makanan_sehat']}<br>";
    if ($d['masyarakat'])     $detail .= "Masyarakat: {$d['masyarakat']}<br>";
    if ($d['jam_tidur'])      $detail .= "Tidur: {$d['jam_tidur']}<br>";
    if ($d['catatan'])        $detail .= "Catatan: {$d['catatan']}";

    echo "<tr>
            <td>{$d['tanggal']}</td>
            <td>{$d['nama_kegiatan']}</td>
            <td>{$detail}</td>
            <td>{$d['nilai']}</td>
          </tr>";
}

echo "</table>";
?>

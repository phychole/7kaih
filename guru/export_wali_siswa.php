<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') exit;

$id_guru = $_SESSION['user_id'];

// FILTER
$filter_tanggal  = $_GET['tanggal'] ?? '';
$filter_kegiatan = $_GET['kegiatan'] ?? '';
$filter_siswa    = $_GET['nama'] ?? '';

// Setting Excel header
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Siswa_Wali.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query siswa wali
$sql_siswa = "
    SELECT s.id, s.nama, s.nisn, k.nama_kelas
    FROM guru_wali_siswa gw
    JOIN siswa s ON gw.id_siswa = s.id
    JOIN kelas k ON s.id_kelas = k.id
    WHERE gw.id_guru = $id_guru
";

if ($filter_siswa != '') {
    $safe_nama = $conn->real_escape_string($filter_siswa);
    $sql_siswa .= " AND s.nama LIKE '%$safe_nama%' ";
}

$sql_siswa .= " ORDER BY s.nama ASC";

$siswa = $conn->query($sql_siswa);

echo "<table border='1'>";
echo "<tr style='background:#ccc;'><th colspan='7'><b>Rekap Jurnal Siswa Wali</b></th></tr>";
echo "
<tr style='background:#f0f0f0;'>
    <th>Nama</th>
    <th>NISN</th>
    <th>Kelas</th>
    <th>Kegiatan</th>
    <th>Tanggal</th>
    <th>Detail</th>
    <th>Status</th>
</tr>
";

while($s = $siswa->fetch_assoc()) {

    $sql_j = "
        SELECT j.*, jk.nama_kegiatan 
        FROM jurnal_siswa j
        JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
        WHERE j.id_siswa = {$s['id']}
    ";

    if ($filter_tanggal != '') {
        $safe_tgl = $conn->real_escape_string($filter_tanggal);
        $sql_j .= " AND j.tanggal = '$safe_tgl' ";
    }

    if ($filter_kegiatan != '') {
        $sql_j .= " AND j.id_kegiatan = ".intval($filter_kegiatan)." ";
    }

    $sql_j .= " ORDER BY j.tanggal DESC";

    $jurnal = $conn->query($sql_j);

    if ($jurnal->num_rows == 0) {
        echo "<tr>
            <td>{$s['nama']}</td>
            <td>{$s['nisn']}</td>
            <td>{$s['nama_kelas']}</td>
            <td colspan='4'>Tidak ada jurnal</td>
        </tr>";
        continue;
    }

    while($d = $jurnal->fetch_assoc()) {

        $detail = "";
        if ($d['jam_bangun']) $detail .= "Bangun: ".$d['jam_bangun']."\n";
        if ($d['ibadah']) $detail .= "Ibadah: ".$d['ibadah']." (".$d['jam_ibadah'].")\n";
        if ($d['olahraga']) $detail .= "Olahraga: ".$d['olahraga']."\n";
        if ($d['belajar']) $detail .= "Belajar: ".$d['belajar']."\n";
        if ($d['makanan_sehat']) $detail .= "Makan Sehat: ".$d['makanan_sehat']."\n";
        if ($d['masyarakat']) $detail .= "Bermasyarakat: ".$d['masyarakat']."\n";
        if ($d['jam_tidur']) $detail .= "Tidur: ".$d['jam_tidur']."\n";
        if ($d['catatan']) $detail .= "Catatan: ".$d['catatan']."\n";

        echo "
        <tr>
            <td>{$s['nama']}</td>
            <td>{$s['nisn']}</td>
            <td>{$s['nama_kelas']}</td>
            <td>{$d['nama_kegiatan']}</td>
            <td>{$d['tanggal']}</td>
            <td>{$detail}</td>
            <td>{$d['nilai']}</td>
        </tr>";
    }
}

echo "</table>";

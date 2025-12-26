<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') exit;

$id_guru  = $_SESSION['user_id'];
$id_kelas = (int)($_GET['id_kelas'] ?? 0);

// Cek akses wali kelas
$kelas = $conn->query("
    SELECT id, nama_kelas
    FROM kelas
    WHERE id = $id_kelas AND id_guru_wali = $id_guru
")->fetch_assoc();

if (!$kelas) exit("Anda bukan wali kelas.");

// FILTER
$filter_tanggal  = $_GET['tanggal'] ?? '';
$filter_kegiatan = $_GET['kegiatan'] ?? '';
$filter_siswa    = $_GET['nama'] ?? '';

// Excel headers
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Wali_Kelas_{$kelas['nama_kelas']}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil siswa kelas
$sql_siswa = "
    SELECT id, nama, nisn
    FROM siswa
    WHERE id_kelas = $id_kelas
";

if ($filter_siswa != '') {
    $safe_nama = $conn->real_escape_string($filter_siswa);
    $sql_siswa .= " AND nama LIKE '%$safe_nama%' ";
}

$sql_siswa .= " ORDER BY nama ASC";

$siswa = $conn->query($sql_siswa);

echo "<table border='1'>";
echo "<tr style='background:#ccc;'>
        <th colspan='7'><b>Rekap Jurnal Kelas {$kelas['nama_kelas']}</b></th>
      </tr>";

echo "
<tr style='background:#f0f0f0;'>
    <th>Nama</th>
    <th>NISN</th>
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
        if ($d['makanan_sehat']) $detail .= "Makanan: ".$d['makanan_sehat']."\n";
        if ($d['masyarakat']) $detail .= "Masyarakat: ".$d['masyarakat']."\n";
        if ($d['jam_tidur']) $detail .= "Tidur: ".$d['jam_tidur']."\n";
        if ($d['catatan']) $detail .= "Catatan: ".$d['catatan']."\n";

        echo "
        <tr>
            <td>{$s['nama']}</td>
            <td>{$s['nisn']}</td>
            <td>{$d['nama_kegiatan']}</td>
            <td>{$d['tanggal']}</td>
            <td>{$detail}</td>
            <td>{$d['nilai']}</td>
        </tr>";
    }
}

echo "</table>";

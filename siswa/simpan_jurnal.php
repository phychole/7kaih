<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_siswa    = $_SESSION['user_id'];
$id_kegiatan = (int)($_POST['id_kegiatan'] ?? 0);

$today = date('Y-m-d'); // tanggal server
$tanggal = $today;

$catatan = trim($_POST['catatan'] ?? '');
$nilai   = $_POST['nilai'] ?? 'Belum'; // default BELUM

if ($id_kegiatan <= 0) {
    header("Location: dashboard.php");
    exit;
}

/* -------------------------------------------------------
   CEK HANYA BOLEH 1X PER HARI (kecuali ibadah & makanan)
--------------------------------------------------------*/
if (!in_array($id_kegiatan, [2,4])) {
    $cek = $conn->prepare("
        SELECT COUNT(*) AS jml 
        FROM jurnal_siswa 
        WHERE id_siswa=? AND id_kegiatan=? AND tanggal=?
    ");
    $cek->bind_param("iis", $id_siswa, $id_kegiatan, $tanggal);
    $cek->execute();
    $r = $cek->get_result()->fetch_assoc();

    if ($r['jml'] > 0) {
        header("Location: riwayat.php?msg=duplikat");
        exit;
    }
}

/* -------------------------------------------------------
   VARIABEL DEFAULT
--------------------------------------------------------*/
$jam_bangun     = NULL;
$jam_tidur      = NULL;
$ibadah         = NULL;
$jam_ibadah     = NULL;
$olahraga       = NULL;
$belajar        = NULL;
$waktu_makan    = NULL;
$makanan_sehat  = NULL;
$masyarakat     = NULL;

/* -------------------------------------------------------
   ISI BERDASARKAN JENIS KEGIATAN
--------------------------------------------------------*/

if ($id_kegiatan == 1) {
    // Bangun pagi
    $jam_bangun = $_POST['jam_bangun'] ?? NULL;

} elseif ($id_kegiatan == 2) {
    // Ibadah
    $ibadah     = trim($_POST['ibadah'] ?? NULL);
    $jam_ibadah = $_POST['jam_ibadah'] ?? NULL;

} elseif ($id_kegiatan == 3) {
    // Olahraga
    if (($_POST['olahraga'] ?? '') == 'Lain-lain') {
        $olahraga = trim($_POST['olahraga_lain'] ?? '');
    } else {
        $olahraga = trim($_POST['olahraga'] ?? '');
    }

} elseif ($id_kegiatan == 4) {
    // Makan sehat
    $waktu_makan = $_POST['waktu_makan'] ?? NULL;

    $list = $_POST['makanan_sehat'] ?? [];
    $lain = trim($_POST['makanan_lain'] ?? '');

    if ($lain != '') {
        $list[] = $lain;
    }

    if (!empty($list)) {
        $makanan_sehat = implode(', ', $list);
    }

} elseif ($id_kegiatan == 5) {
    // Belajar
    if (($_POST['belajar'] ?? '') == 'Lain-lain') {
        $belajar = trim($_POST['belajar_lain'] ?? '');
    } else {
        $belajar = trim($_POST['belajar'] ?? '');
    }

} elseif ($id_kegiatan == 6) {
    // Bermasyarakat
    if (($_POST['masyarakat'] ?? '') == 'Lain-lain') {
        $masyarakat = trim($_POST['masyarakat_lain'] ?? '');
    } else {
        $masyarakat = trim($_POST['masyarakat'] ?? '');
    }

} elseif ($id_kegiatan == 7) {
    // Tidur cepat
    $jam_tidur = $_POST['jam_tidur'] ?? NULL;
}

/* -------------------------------------------------------
   SIMPAN KE DATABASE
--------------------------------------------------------*/

$stmt = $conn->prepare("
    INSERT INTO jurnal_siswa 
    (id_siswa, id_kegiatan, tanggal, jam_bangun, jam_tidur, ibadah, jam_ibadah, olahraga, belajar, waktu_makan, makanan_sehat, masyarakat, catatan, nilai)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "iissssssssssss",
    $id_siswa,
    $id_kegiatan,
    $tanggal,
    $jam_bangun,
    $jam_tidur,
    $ibadah,
    $jam_ibadah,
    $olahraga,
    $belajar,
    $waktu_makan,
    $makanan_sehat,
    $masyarakat,
    $catatan,
    $nilai
);

if (!$stmt->execute()) {
    die("Gagal menyimpan: " . $conn->error);
}

header("Location: riwayat.php?msg=sukses");
exit;

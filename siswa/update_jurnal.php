<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') exit;

$id_siswa = $_SESSION['user_id'];
$id       = (int)($_POST['id'] ?? 0);

// Ambil id_kegiatan lama
$q = $conn->prepare("SELECT id_kegiatan FROM jurnal_siswa WHERE id=? AND id_siswa=?");
$q->bind_param("ii", $id, $id_siswa);
$q->execute();
$res = $q->get_result()->fetch_assoc();

if (!$res) {
    exit("Data tidak ditemukan.");
}

$id_kegiatan = (int)$res['id_kegiatan'];

// Default nilai input
$catatan = trim($_POST['catatan'] ?? '');
$nilai   = $_POST['nilai'] ?? 'Belum';

// Set semua variabel NULL terlebih dahulu
$jam_bangun    = NULL;
$jam_tidur     = NULL;
$ibadah        = NULL;
$jam_ibadah    = NULL;
$olahraga      = NULL;
$belajar       = NULL;
$waktu_makan   = NULL;
$makanan_sehat = NULL;
$masyarakat    = NULL;

/* -------------------------------------------------------
   ISI FIELD SESUAI JENIS KEGIATAN
-------------------------------------------------------- */

if ($id_kegiatan == 1) {
    // Bangun pagi
    $jam_bangun = $_POST['jam_bangun'] ?? NULL;

} elseif ($id_kegiatan == 2) {
    // Ibadah
    $ibadah     = trim($_POST['ibadah'] ?? '');
    $jam_ibadah = $_POST['jam_ibadah'] ?? NULL;

} elseif ($id_kegiatan == 3) {
    // Olahraga
    if (($_POST['olahraga'] ?? '') === 'Lain-lain') {
        $olahraga = trim($_POST['olahraga_lain'] ?? '');
    } else {
        $olahraga = trim($_POST['olahraga'] ?? '');
    }

} elseif ($id_kegiatan == 4) {
    // Makan sehat
    $waktu_makan = $_POST['waktu_makan'] ?? NULL;

    $list = $_POST['makanan_sehat'] ?? [];
    $lain = trim($_POST['makanan_lain'] ?? '');

    if ($lain !== '') $list[] = $lain;

    if (!empty($list)) {
        $makanan_sehat = implode(', ', $list);
    }

} elseif ($id_kegiatan == 5) {
    // Belajar
    if (($_POST['belajar'] ?? '') === 'Lain-lain') {
        $belajar = trim($_POST['belajar_lain'] ?? '');
    } else {
        $belajar = trim($_POST['belajar'] ?? '');
    }

} elseif ($id_kegiatan == 6) {
    // Bermasyarakat
    if (($_POST['masyarakat'] ?? '') === 'Lain-lain') {
        $masyarakat = trim($_POST['masyarakat_lain'] ?? '');
    } else {
        $masyarakat = trim($_POST['masyarakat'] ?? '');
    }

} elseif ($id_kegiatan == 7) {
    // Tidur cepat
    $jam_tidur = $_POST['jam_tidur'] ?? NULL;
}

/* -------------------------------------------------------
   UPDATE DATABASE
-------------------------------------------------------- */

$stmt = $conn->prepare("
    UPDATE jurnal_siswa 
    SET 
        jam_bangun=?, 
        jam_tidur=?, 
        ibadah=?, 
        jam_ibadah=?, 
        olahraga=?, 
        belajar=?, 
        waktu_makan=?, 
        makanan_sehat=?, 
        masyarakat=?, 
        catatan=?, 
        nilai=?
    WHERE id=? AND id_siswa=?
");

$stmt->bind_param(
    "sssssssssssii",
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
    $nilai,
    $id,
    $id_siswa
);

$stmt->execute();

header("Location: riwayat.php?msg=updated");
exit;

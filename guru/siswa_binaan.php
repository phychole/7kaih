<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') {
    header("Location: ../login.php");
    exit;
}

$id_guru = $_SESSION['user_id'];

// 1. Ambil siswa binaan dari tabel relasi
$sqlBinaan = "
    SELECT s.*, k.nama_kelas 
    FROM guru_wali_siswa gw
    JOIN siswa s ON gw.id_siswa = s.id
    JOIN kelas k ON s.id_kelas = k.id
    WHERE gw.id_guru = $id_guru
    ORDER BY s.nama ASC
";
$binaan = $conn->query($sqlBinaan);

// 2. Ambil siswa dari kelas bila guru ini wali kelas
$sqlKelas = "
    SELECT s.*, k.nama_kelas 
    FROM kelas k
    JOIN siswa s ON s.id_kelas = k.id
    WHERE k.id_guru_wali = $id_guru
    ORDER BY s.nama ASC
";
$waliKelas = $conn->query($sqlKelas);
?>
<!DOCTYPE html>
<html>
<head>
<title>Siswa Binaan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../guru/layout/header.php'; ?>

<div class="container mt-4">

<h3><i class="fas fa-users"></i> Siswa Binaan Saya</h3>

<h5 class="mt-4">Sebagai Pembina</h5>
<table class="table table-bordered">
<thead><tr><th>Nama</th><th>Kelas</th><th>Aksi</th></tr></thead>
<tbody>
<?php while($s = $binaan->fetch_assoc()): ?>
<tr>
  <td><?= $s['nama'] ?></td>
  <td><?= $s['nama_kelas'] ?></td>
  <td>
    <a href="lihat_jurnal.php?id_siswa=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Lihat Jurnal</a>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>


<h5 class="mt-5">Sebagai Wali Kelas</h5>
<table class="table table-bordered">
<thead><tr><th>Nama</th><th>Kelas</th><th>Aksi</th></tr></thead>
<tbody>
<?php while($s = $waliKelas->fetch_assoc()): ?>
<tr>
  <td><?= $s['nama'] ?></td>
  <td><?= $s['nama_kelas'] ?></td>
  <td>
    <a href="lihat_jurnal.php?id_siswa=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Lihat Jurnal</a>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>

</body>
</html>

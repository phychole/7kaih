\<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_siswa = $_SESSION['user_id'];

// Ambil filter
$filter_tanggal = $_GET['tanggal'] ?? '';
$filter_kegiatan = $_GET['id_kegiatan'] ?? '';

// Ambil kegiatan dropdown
$kegiatanList = $conn->query("SELECT * FROM jenis_kegiatan ORDER BY nama_kegiatan ASC");

// QUERY UTAMA
$sql = "
    SELECT j.*, jk.nama_kegiatan 
    FROM jurnal_siswa j
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    WHERE j.id_siswa = ?
";

if ($filter_tanggal != '') {
    $safe = $conn->real_escape_string($filter_tanggal);
    $sql .= " AND j.tanggal = '$safe' ";
}

if ($filter_kegiatan != '') {
    $sql .= " AND j.id_kegiatan = ".intval($filter_kegiatan)." ";
}

$sql .= " ORDER BY j.tanggal DESC, j.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Riwayat Jurnal</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f4f6f9; }

.container {
    max-width: 900px;
}

/* TABLE RESPONSIVE SCROLL */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Card filter lebih mobile-friendly */
.filter-card {
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
}

/* Ukuran font tabel lebih besar di mobile */
@media(max-width: 576px) {
    table td, table th {
        font-size: 14px;
        padding: 8px;
    }
    h4 { font-size: 20px; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Riwayat Jurnal</span>
    <a href="index.php" class="btn btn-outline-light btn-sm">Kembali</a>
  </div>
</nav>

<div class="container mt-4">

<h4 class="mb-3 fw-bold">Riwayat Jurnal Saya</h4>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg']=='sukses'): ?>
        <div class="alert alert-success">Jurnal berhasil disimpan.</div>
    <?php elseif ($_GET['msg']=='duplikat'): ?>
        <div class="alert alert-warning">Kegiatan ini sudah diisi hari ini.</div>
    <?php elseif ($_GET['msg']=='deleted'): ?>
        <div class="alert alert-info">Jurnal berhasil dihapus.</div>
    <?php endif; ?>
<?php endif; ?>

<!-- FILTER -->
<div class="card mb-3 shadow-sm filter-card">
<div class="card-body">

<form class="row g-3" method="GET">

    <div class="col-12 col-md-4">
        <label class="form-label"><b>Tanggal</b></label>
        <input type="date" name="tanggal" value="<?=htmlspecialchars($filter_tanggal)?>" class="form-control">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label"><b>Jenis Kegiatan</b></label>
        <select name="id_kegiatan" class="form-select">
            <option value="">Semua</option>
            <?php while($kg = $kegiatanList->fetch_assoc()): ?>
            <option value="<?=$kg['id']?>" <?=($filter_kegiatan==$kg['id']?'selected':'')?>>
                <?=htmlspecialchars($kg['nama_kegiatan'])?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-12 col-md-4 d-flex align-items-end">
        <button class="btn btn-primary me-2 w-50">Tampilkan</button>
        <a href="riwayat.php" class="btn btn-secondary w-50">Reset</a>
    </div>

</form>

</div>
</div>

<!-- TABEL RESPONSIVE -->
<div class="table-responsive">
<table class="table table-bordered table-striped mt-3">
<thead class="table-primary text-center">
<tr>
  <th>Tanggal</th>
  <th>Kegiatan</th>
  <th>Detail</th>
  <th>Status</th>
  <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php if ($res->num_rows == 0): ?>
<tr><td colspan="5" class="text-center text-danger">Tidak ada jurnal.</td></tr>

<?php else: ?>
<?php while($d = $res->fetch_assoc()): ?>
<tr>
  <td class="text-center"><?=htmlspecialchars($d['tanggal'])?></td>
  <td><?=htmlspecialchars($d['nama_kegiatan'])?></td>

  <td>
    <?php
    switch ($d['id_kegiatan']) {
        case 1: echo "Jam bangun: ".$d['jam_bangun']; break;
        case 2: echo "Ibadah: ".$d['ibadah']." (".$d['jam_ibadah'].")"; break;
        case 3: echo "Olahraga: ".$d['olahraga']; break;
        case 4:
            echo "Waktu makan: ".$d['waktu_makan']."<br>";
            if ($d['makanan_sehat']) echo "Menu: ".$d['makanan_sehat'];
            break;
        case 5: echo "Belajar: ".$d['belajar']; break;
        case 6: echo "Masyarakat: ".$d['masyarakat']; break;
        case 7: echo "Jam tidur: ".$d['jam_tidur']; break;
    }

    if ($d['catatan']) {
        echo "<br><i>Catatan:</i> ".nl2br(htmlspecialchars($d['catatan']));
    }
    ?>
  </td>

  <td class="text-center">
    <?php if ($d['nilai']=='Sudah'): ?>
      <span class="badge bg-success">Sudah</span>
    <?php else: ?>
      <span class="badge bg-danger">Belum</span>
    <?php endif; ?>
  </td>

  <td class="text-center">
    <a href="edit_jurnal.php?id=<?=$d['id']?>" class="btn btn-warning btn-sm mb-1 w-100">Edit</a>
    <a href="hapus_jurnal.php?id=<?=$d['id']?>" onclick="return confirm('Hapus jurnal ini?')" class="btn btn-danger btn-sm w-100">Hapus</a>
  </td>
</tr>
<?php endwhile; ?>
<?php endif; ?>

</tbody>
</table>
</div>

</div>

<script src="https://kit.fontawesome.com/a2d9d6c36e.js"></script>
</body>
</html>

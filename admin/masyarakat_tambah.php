<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php';

if ($_SESSION['role'] != 'admin') exit;

if ($_SERVER['REQUEST_METHOD']=="POST") {

    $nama = trim($_POST['nama']);

    // cek duplikat
    $cek = $conn->prepare("SELECT id FROM jenis_masyarakat WHERE nama=?");
    $cek->bind_param("s", $nama);
    $cek->execute();

    if ($cek->get_result()->num_rows > 0) {
        $error = "Nama kegiatan sudah ada!";
    } else {
        $stmt = $conn->prepare("INSERT INTO jenis_masyarakat (nama) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        header("Location: masyarakat.php?msg=add");
        exit;
    }
}
?>

<div class="content">

<h3><i class="fas fa-plus-circle"></i> Tambah Jenis Kegiatan Bermasyarakat</h3>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card shadow"><div class="card-body">

<form method="POST">

    <div class="mb-3">
        <label><b>Nama Kegiatan</b></label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
    <a href="masyarakat.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>

</form>

</div></div>

</div>

<?php include 'layout/footer.php'; ?>

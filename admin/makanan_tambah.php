<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php';

if ($_SESSION['role'] != 'admin') exit;

if ($_SERVER['REQUEST_METHOD']=="POST") {

    $nama = trim($_POST['nama']);

    // cek duplikat
    $cek = $conn->prepare("SELECT id FROM jenis_makanan WHERE nama=?");
    $cek->bind_param("s", $nama);
    $cek->execute();

    if ($cek->get_result()->num_rows > 0) {
        $error = "Nama makanan/minuman sudah ada!";
    } else {
        $stmt = $conn->prepare("INSERT INTO jenis_makanan (nama) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        header("Location: makanan.php?msg=add");
        exit;
    }
}
?>

<div class="content">

<h3><i class="fas fa-plus-circle"></i> Tambah Jenis Makanan/Minuman</h3>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card shadow"><div class="card-body">

<form method="POST">
    <div class="mb-3">
        <label><b>Nama Makanan / Minuman</b></label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
    <a href="makanan.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</form>

</div></div>

</div>

<?php include 'layout/footer.php'; ?>

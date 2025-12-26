<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

if ($_SESSION['role'] != 'admin') exit;

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $nama = trim($_POST['nama']);

    // Cek duplikasi
    $cek = $conn->prepare("SELECT id FROM jenis_olahraga WHERE nama=?");
    $cek->bind_param("s", $nama);
    $cek->execute();
    $res = $cek->get_result();

    if ($res->num_rows > 0) {
        $error = "Nama jenis olahraga sudah ada, gunakan nama lain.";
    } else {
        // INSERT data
        $stmt = $conn->prepare("INSERT INTO jenis_olahraga (nama) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();

        header("Location: olahraga.php?msg=add");
        exit;
    }
}
?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-plus-circle"></i> Tambah Jenis Olahraga</h3>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card shadow">
<div class="card-body">

<form method="POST">

    <div class="mb-3">
        <label class="form-label"><b>Nama Olahraga</b></label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <button class="btn btn-primary">
        <i class="fas fa-save"></i> Simpan
    </button>

    <a href="olahraga.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

</form>

</div>
</div>

</div>

<?php include 'layout/footer.php'; ?>

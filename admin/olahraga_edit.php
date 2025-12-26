<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

if ($_SESSION['role'] != 'admin') exit;

// Ambil ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data olahraga
$stmt = $conn->prepare("SELECT * FROM jenis_olahraga WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<div class='content'><div class='alert alert-danger'>Data tidak ditemukan.</div></div>";
    include 'layout/footer.php';
    exit;
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama = trim($_POST['nama']);

    // Cek nama duplikat
    $cek = $conn->prepare("SELECT id FROM jenis_olahraga WHERE nama=? AND id<>?");
    $cek->bind_param("si", $nama, $id);
    $cek->execute();
    $cekRes = $cek->get_result();

    if ($cekRes->num_rows > 0) {
        $error = "Nama jenis olahraga sudah ada, gunakan nama lain.";
    } else {
        // Update data
        $update = $conn->prepare("UPDATE jenis_olahraga SET nama=? WHERE id=?");
        $update->bind_param("si", $nama, $id);
        $update->execute();

        header("Location: olahraga.php?msg=updated");
        exit;
    }
}
?>

<div class="content">

<h3 class="mb-4">
    <i class="fas fa-edit"></i> Edit Jenis Olahraga
</h3>

<?php if (!empty($error)): ?>
<div class="alert alert-danger">
    <?= $error ?>
</div>
<?php endif; ?>

<div class="card shadow">
<div class="card-body">

<form method="POST">

    <div class="mb-3">
        <label class="form-label"><b>Nama Olahraga</b></label>
        <input type="text" name="nama" class="form-control" 
               value="<?= htmlspecialchars($data['nama']) ?>" required>
    </div>

    <button class="btn btn-primary">
        <i class="fas fa-save"></i> Update
    </button>

    <a href="olahraga.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

</form>

</div>
</div>

</div>

<?php include 'layout/footer.php'; ?>

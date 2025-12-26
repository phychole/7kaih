<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php';

if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM jenis_masyarakat WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<div class='content'><div class='alert alert-danger'>Data tidak ditemukan.</div></div>";
    include 'layout/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD']=="POST") {

    $nama = trim($_POST['nama']);

    // cek duplikat
    $cek = $conn->prepare("SELECT id FROM jenis_masyarakat WHERE nama=? AND id<>?");
    $cek->bind_param("si", $nama, $id);
    $cek->execute();

    if ($cek->get_result()->num_rows > 0) {
        $error = "Nama kegiatan sudah digunakan!";
    } else {
        $upd = $conn->prepare("UPDATE jenis_masyarakat SET nama=? WHERE id=?");
        $upd->bind_param("si", $nama, $id);
        $upd->execute();
        header("Location: masyarakat.php?msg=updated");
        exit;
    }
}
?>

<div class="content">

<h3><i class="fas fa-edit"></i> Edit Jenis Kegiatan Bermasyarakat</h3>

<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card shadow"><div class="card-body">

<form method="POST">
    <div class="mb-3">
        <label><b>Nama Kegiatan</b></label>
        <input type="text" name="nama" class="form-control" 
               value="<?= htmlspecialchars($data['nama']) ?>" required>
    </div>

    <button class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
    <a href="masyarakat.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</form>

</div></div>

</div>

<?php include 'layout/footer.php'; ?>

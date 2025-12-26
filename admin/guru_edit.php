<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: guru.php?msg=err"); exit; }

$error = "";

// ambil data
$stmt = $conn->prepare("SELECT * FROM guru WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$guru = $stmt->get_result()->fetch_assoc();
if (!$guru) { header("Location: guru.php?msg=err"); exit; }

// proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $nip   = trim($_POST['nip'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $jk    = $_POST['jk'] ?? 'L';
    $agama = $_POST['agama'] ?? 'Islam';

    if ($nama === '' || $email === '') {
        $error = "Nama dan Email wajib diisi.";
    } else {

        // cek email dipakai guru lain
        $cek = $conn->prepare("SELECT id FROM guru WHERE email=? AND id<>? LIMIT 1");
        $cek->bind_param("si", $email, $id);
        $cek->execute();
        $cekRes = $cek->get_result();

        if ($cekRes->num_rows > 0) {
            $error = "Email sudah digunakan guru lain.";
        } else {
            $up = $conn->prepare("
                UPDATE guru 
                SET nama=?, nip=?, email=?, jenis_kelamin=?, agama=? 
                WHERE id=?
            ");
            $up->bind_param("sssssi", $nama, $nip, $email, $jk, $agama, $id);

            if ($up->execute()) {
                header("Location: guru.php?msg=edit");
                exit;
            } else {
                $error = "Gagal update: " . $conn->error;
            }
        }
    }
}

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-edit"></i> Edit Guru</h3>

<?php if($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow">
  <div class="card-body">
    <form method="POST" autocomplete="off">

      <div class="mb-3">
        <label class="form-label"><b>Nama</b></label>
        <input type="text" name="nama" class="form-control" required
               value="<?= htmlspecialchars($_POST['nama'] ?? $guru['nama']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label"><b>NIP (Opsional)</b></label>
        <input type="text" name="nip" class="form-control"
               value="<?= htmlspecialchars($_POST['nip'] ?? $guru['nip']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label"><b>Email</b></label>
        <input type="email" name="email" class="form-control" required
               value="<?= htmlspecialchars($_POST['email'] ?? $guru['email']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label"><b>Jenis Kelamin</b></label>
        <?php $jkVal = $_POST['jk'] ?? $guru['jenis_kelamin']; ?>
        <select name="jk" class="form-select">
          <option value="L" <?= ($jkVal=='L'?'selected':'') ?>>Laki-laki</option>
          <option value="P" <?= ($jkVal=='P'?'selected':'') ?>>Perempuan</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label"><b>Agama</b></label>
        <?php $agamaVal = $_POST['agama'] ?? $guru['agama']; ?>
        <select name="agama" class="form-select" required>
          <?php foreach(["Islam","Kristen","Katolik","Hindu","Budha","Konghucu"] as $ag): ?>
            <option value="<?= $ag ?>" <?= ($agamaVal==$ag?'selected':'') ?>><?= $ag ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary">
          <i class="fas fa-save"></i> Simpan Perubahan
        </button>
        <a href="guru.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>

    </form>
  </div>
</div>

</div>

<?php include 'layout/footer.php'; ?>

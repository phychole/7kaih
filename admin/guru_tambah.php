<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

// =======================
// PROSES POST DULU (SEBELUM INCLUDE LAYOUT)
// =======================
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $nama  = trim($_POST['nama'] ?? '');
    $nip   = trim($_POST['nip'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $jk    = $_POST['jk'] ?? 'L';
    $agama = $_POST['agama'] ?? 'Islam';
    $password_plain = $_POST['password'] ?? '';

    // Validasi sederhana
    if ($nama === '' || $email === '' || $password_plain === '') {
        $error = "Nama, Email, dan Password wajib diisi.";
    } else {

        // Cek email duplikat
        $cek = $conn->prepare("SELECT id FROM guru WHERE email=? LIMIT 1");
        $cek->bind_param("s", $email);
        $cek->execute();
        $cekRes = $cek->get_result();

        if ($cekRes->num_rows > 0) {
            $error = "Email sudah digunakan. Silakan pakai email lain.";
        } else {

            $pass = password_hash($password_plain, PASSWORD_DEFAULT);

            // Insert
            $stmt = $conn->prepare("
                INSERT INTO guru (nama, nip, email, password, jenis_kelamin, agama)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssss", $nama, $nip, $email, $pass, $jk, $agama);

            if ($stmt->execute()) {
                header("Location: guru.php?msg=add");
                exit;
            } else {
                $error = "Gagal menyimpan data guru: " . $conn->error;
            }
        }
    }
}

// =======================
// BARU INCLUDE LAYOUT SETELAH POST DIPROSES
// =======================
include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="content">

<h3 class="mb-4">
    <i class="fas fa-plus-circle"></i> Tambah Guru
</h3>

<?php if (!empty($error)): ?>
<div class="alert alert-danger">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="card shadow">
<div class="card-body">

<form method="POST" autocomplete="off">

<div class="mb-3">
    <label class="form-label"><b>Nama</b></label>
    <input type="text" name="nama" class="form-control" required
           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
</div>

<div class="mb-3">
    <label class="form-label"><b>NIP (Opsional)</b></label>
    <input type="text" name="nip" class="form-control"
           value="<?= htmlspecialchars($_POST['nip'] ?? '') ?>">
</div>

<div class="mb-3">
    <label class="form-label"><b>Email</b></label>
    <input type="email" name="email" class="form-control" required
           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
</div>

<div class="mb-3">
    <label class="form-label"><b>Password</b></label>
    <input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label"><b>Jenis Kelamin</b></label>
    <select name="jk" class="form-select">
        <option value="L" <?= (($_POST['jk'] ?? '')=='L'?'selected':'') ?>>Laki-laki</option>
        <option value="P" <?= (($_POST['jk'] ?? '')=='P'?'selected':'') ?>>Perempuan</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label"><b>Agama</b></label>
    <select name="agama" class="form-select" required>
        <?php
        $agamaList = ["Islam","Kristen","Katolik","Hindu","Budha","Konghucu"];
        $agamaNow = $_POST['agama'] ?? 'Islam';
        foreach($agamaList as $ag):
        ?>
            <option value="<?= $ag ?>" <?= ($agamaNow==$ag?'selected':'') ?>>
                <?= $ag ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">
        <i class="fas fa-save"></i> Simpan
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

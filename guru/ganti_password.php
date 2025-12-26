<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include 'layout/header.php';
include 'layout/sidebar.php';

$id_guru = (int)$_SESSION['user_id'];
$notif = "";
$err = "";

// HANDLE UPDATE PASSWORD
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $old = $_POST['old'];
    $new = $_POST['new'];
    $confirm = $_POST['confirm'];

    // Cek konfirmasi password
    if ($new !== $confirm) {
        $err = "Password baru dan konfirmasi tidak sama!";
    } else {

        // Ambil password lama
        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $id_guru);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (!$res || !password_verify($old, $res['password'])) {
            $err = "Password lama salah!";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);

            $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $upd->bind_param("si", $hash, $id_guru);
            $upd->execute();

            $notif = "Password berhasil diperbarui!";
        }
    }
}
?>

<div class="content">

    <h3 class="mb-3">
        <i class="fas fa-key"></i> Ganti Password
    </h3>

    <?php if ($err): ?>
        <div class="alert alert-danger"><?= $err ?></div>
    <?php endif; ?>

    <?php if ($notif): ?>
        <div class="alert alert-success"><?= $notif ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST" class="row g-3">

                <div class="col-md-12">
                    <label class="form-label"><b>Password Lama</b></label>
                    <input type="password" name="old" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><b>Password Baru</b></label>
                    <input type="password" name="new" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><b>Konfirmasi Password Baru</b></label>
                    <input type="password" name="confirm" class="form-control" required>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Password
                    </button>

                    <a href="index.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

<?php include 'layout/footer.php'; ?>

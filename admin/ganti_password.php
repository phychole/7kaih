<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $old = $_POST['old'];
    $new = $_POST['new'];

    // Ambil password lama
    $cek = $conn->query("SELECT password FROM users WHERE id=$id")->fetch_assoc();

    if (!$cek || !password_verify($old, $cek['password'])) {
        $error = "Password lama salah!";
    } else {
        $newhash = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$newhash' WHERE id=$id");
        $success = "Password berhasil diperbarui.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Ganti Password Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://kit.fontawesome.com/a2d9d6c36e.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<!-- CONTENT dari sidebar.php, jadi tidak perlu buka div baru -->
<div class="content">

<h3><i class="fas fa-key"></i> Ganti Password Admin</h3>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if(isset($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="card shadow p-4" style="max-width:500px;">
<form method="POST">

    <div class="mb-3">
        <label class="form-label">Password Lama</label>
        <input type="password" name="old" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Password Baru</label>
        <input type="password" name="new" class="form-control" required>
    </div>

    <button class="btn btn-primary">
        <i class="fas fa-save"></i> Update Password
    </button>

</form>
</div>

</div> <!-- end content -->

<?php include 'layout/footer.php'; ?>

</body>
</html>

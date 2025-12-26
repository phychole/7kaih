<?php
require '../config.php';
require '../auth.php';
if ($_SESSION['role'] != 'admin') exit;

if ($_SERVER['REQUEST_METHOD']=='POST') {
    $nama  = $_POST['nama'];
    $email = $_POST['email'];
    $role  = $_POST['role'];
    $pass  = password_hash('123456', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (nama,email,password,role) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss",$nama,$email,$pass,$role);
    $stmt->execute();

    header("Location: user.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Tambah User</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
<h3>Tambah User</h3>

<form method="POST">

<div class="mb-3">
<label>Nama</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Role</label>
<select name="role" class="form-control">
    <option value="admin">Admin</option>
    <option value="kurikulum">Kurikulum</option>
</select>
</div>

<p class="text-muted">Password default: <b>123456</b></p>

<button class="btn btn-primary">Simpan</button>
<a href="user.php" class="btn btn-secondary">Kembali</a>

</form>
</div>

</body>
</html>

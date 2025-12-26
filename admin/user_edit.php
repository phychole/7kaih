<?php
require '../config.php';
require '../auth.php';
if ($_SESSION['role'] != 'admin') exit;

$id = (int)($_GET['id'] ?? 0);
$data = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if (!$data) exit("User tidak ditemukan");

if ($_SERVER['REQUEST_METHOD']=="POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi",$nama,$email,$role,$id);
    $stmt->execute();

    header("Location: user.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
<h3>Edit User</h3>

<form method="POST">

<div class="mb-3">
<label>Nama</label>
<input type="text" name="nama" class="form-control" value="<?= $data['nama'] ?>" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?= $data['email'] ?>" required>
</div>

<div class="mb-3">
<label>Role</label>
<select name="role" class="form-control">
    <option value="admin" <?= $data['role']=='admin'?'selected':'' ?>>Admin</option>
    <option value="kurikulum" <?= $data['role']=='kurikulum'?'selected':'' ?>>Kurikulum</option>
</select>
</div>

<button class="btn btn-primary">Update</button>
<a href="user.php" class="btn btn-secondary">Kembali</a>

</form>

</div>
</body>
</html>

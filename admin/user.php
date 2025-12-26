<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') exit;

$users = $conn->query("SELECT * FROM users ORDER BY role ASC, nama ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manajemen User</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://kit.fontawesome.com/a2d9d6c36e.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<!-- JANGAN buat div content lagi, SIDEBAR sudah memulai div-content -->
<div class="content">

<h3><i class="fas fa-users-cog"></i> Manajemen User</h3>

<a href="user_tambah.php" class="btn btn-primary mb-3">
    <i class="fas fa-user-plus"></i> Tambah User
</a>

<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Nama</th>
    <th>Email</th>
    <th>Role</th>
    <th width="180px">Aksi</th>
</tr>
</thead>
<tbody>

<?php while($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($u['nama']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['role']) ?></td>
    <td>
        <a href="user_edit.php?id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="user_reset.php?id=<?= $u['id'] ?>" 
           onclick="return confirm('Reset password ke: 123456 ?')"
           class="btn btn-info btn-sm">
            <i class="fas fa-key"></i> Reset
        </a>
        <a href="user_hapus.php?id=<?= $u['id'] ?>" 
           onclick="return confirm('Hapus user?')"
           class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Hapus
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div> <!-- end content -->

<?php include 'layout/footer.php'; ?>

</body>
</html>

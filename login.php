<?php
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role     = $_POST['role'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($role == 'admin' || $role == 'kurikulum') {
        $qry = $conn->prepare("SELECT * FROM users WHERE email=? AND role=? LIMIT 1");
        $qry->bind_param("ss", $username, $role);
    } elseif ($role == 'guru') {
        $qry = $conn->prepare("SELECT * FROM guru WHERE email=? LIMIT 1");
        $qry->bind_param("s", $username);
    } else {
        $qry = $conn->prepare("SELECT * FROM siswa WHERE nisn=? LIMIT 1");
        $qry->bind_param("s", $username);
    }

    $qry->execute();
    $res = $qry->get_result();
    $data = $res->fetch_assoc();

    if ($data) {
        if (password_verify($password, $data['password'])) {

            $_SESSION['role'] = $role;
            $_SESSION['user_id'] = $data['id'];
            $_SESSION['nama'] = $data['nama'];

            if ($role == 'admin') header("Location: admin/index.php");
            if ($role == 'kurikulum') header("Location: kurikulum/index.php");
            if ($role == 'guru') header("Location: guru/index.php");
            if ($role == 'siswa') header("Location: siswa/index.php");
            exit;

        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Pengguna tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background:#f0f2f5;
        display:flex;
        align-items:center;
        justify-content:center;
        height:100vh;
        padding:15px;
    }

    .login-card {
        max-width:420px;
        width:100%;
        border-radius:12px;
    }

    .login-title {
        font-size:1.4rem;
        font-weight:600;
    }

    /* Agar input tidak terlalu kecil di HP */
    input, select {
        height:48px !important;
        font-size:1rem;
    }

    button {
        height:48px;
        font-size:1.05rem;
    }
</style>
</head>
<body>

<div class="card shadow login-card">
    <div class="card-header bg-primary text-white text-center login-title">
        Jurnal KAIH <p> (Kebiasan Anak Indonesia Hebat)</p>
    </div>

    <div class="card-body">

        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label><b>Role</b></label>
                <select name="role" class="form-select">
                    <option value="admin">Admin</option>
                    <option value="kurikulum">Kurikulum</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>

            <div class="mb-3">
                <label><b>Email / NISN</b></label>
                <input type="text" name="username" 
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label><b>Password</b></label>
                <input type="password" name="password" 
                       class="form-control" required>
            </div>

            <button class="btn btn-primary w-100 mt-2">
                Masuk
            </button>

        </form>

    </div>
</div>

</body>
</html>

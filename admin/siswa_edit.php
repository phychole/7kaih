<?php
require '../config.php';
require '../auth.php';

if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: siswa.php?msg=invalid");
    exit;
}

// Ambil data siswa (sebelum include)
$q = $conn->prepare("SELECT * FROM siswa WHERE id=? LIMIT 1");
$q->bind_param("i", $id);
$q->execute();
$data = $q->get_result()->fetch_assoc();

if (!$data) {
    header("Location: siswa.php?msg=notfound");
    exit;
}

// Submit update (SEBELUM INCLUDE LAYOUT agar header() aman)
$error = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $nisn  = trim($_POST['nisn'] ?? '');
    $nama  = trim($_POST['nama'] ?? '');
    $jk    = $_POST['jk'] ?? 'L';
    $agama = $_POST['agama'] ?? '';
    $kelas = (int)($_POST['id_kelas'] ?? 0);
    $password_input = $_POST['password'] ?? '';

    if ($nisn === '' || $nama === '' || $kelas <= 0) {
        $error = "Mohon lengkapi data yang wajib diisi.";
    } else {

        // cek nisn duplikat
        $cek = $conn->prepare("SELECT id FROM siswa WHERE nisn=? AND id<>? LIMIT 1");
        $cek->bind_param("si", $nisn, $id);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $error = "NISN sudah digunakan siswa lain!";
        } else {

            // FOTO
            $fotoName = $data['foto'] ?: 'default.png';

            if (!empty($_FILES['foto']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allow = ['jpg','jpeg','png','webp'];

                if (!in_array($ext, $allow)) {
                    $error = "Format foto harus jpg/jpeg/png/webp.";
                } else {
                    $folder = "../uploads/siswa/";
                    if (!is_dir($folder)) @mkdir($folder, 0777, true);

                    // hapus foto lama
                    if ($fotoName != "default.png" && file_exists($folder.$fotoName)) {
                        @unlink($folder.$fotoName);
                    }

                    $fotoName = "siswa_" . time() . "_" . rand(1000,9999) . "." . $ext;
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $folder.$fotoName)) {
                        $error = "Upload foto gagal.";
                    }
                }
            }

            if ($error === "") {
                // password update?
                if ($password_input !== "") {
                    $pass = password_hash($password_input, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("
                        UPDATE siswa
                        SET nisn=?, nama=?, jenis_kelamin=?, agama=?, id_kelas=?, password=?, foto=?
                        WHERE id=?
                    ");
                    $stmt->bind_param(
                        "ssssissi",
                        $nisn, $nama, $jk, $agama, $kelas, $pass, $fotoName, $id
                    );
                } else {
                    $stmt = $conn->prepare("
                        UPDATE siswa
                        SET nisn=?, nama=?, jenis_kelamin=?, agama=?, id_kelas=?, foto=?
                        WHERE id=?
                    ");
                    // id_kelas harus integer
                    $stmt->bind_param(
                        "ssssis i",
                        $nisn, $nama, $jk, $agama, $kelas, $fotoName, $id
                    );
                }

                // NOTE: perbaiki typo bind_param format di atas (hapus spasi)
                // Saya tulis ulang yang benar agar aman:
                if ($password_input !== "") {
                    // sudah benar
                } else {
                    $stmt->close();
                    $stmt = $conn->prepare("
                        UPDATE siswa
                        SET nisn=?, nama=?, jenis_kelamin=?, agama=?, id_kelas=?, foto=?
                        WHERE id=?
                    ");
                    $stmt->bind_param("ssssisi", $nisn, $nama, $jk, $agama, $kelas, $fotoName, $id);
                }

                $stmt->execute();

                header("Location: siswa.php?msg=updated");
                exit;
            }
        }
    }
}

// Ambil kelas list (SETELAH proses POST OK)
$kelasList = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");

// BARU INCLUDE LAYOUT
include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-edit"></i> Edit Siswa</h3>
        <a href="siswa.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label"><b>NISN</b></label>
                        <input type="text" name="nisn" value="<?= htmlspecialchars($data['nisn']) ?>" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><b>Nama</b></label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><b>Jenis Kelamin</b></label>
                        <select name="jk" class="form-select">
                            <option value="L" <?= ($data['jenis_kelamin']=='L'?'selected':'') ?>>Laki-Laki</option>
                            <option value="P" <?= ($data['jenis_kelamin']=='P'?'selected':'') ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><b>Agama</b></label>
                        <select name="agama" class="form-select">
                            <?php
                            $ags = ["Islam","Kristen","Katolik","Hindu","Budha","Konghucu"];
                            foreach ($ags as $a):
                                $sel = ($data['agama'] == $a) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($a) ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($a) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><b>Kelas</b></label>
                        <select name="id_kelas" class="form-select" required>
                            <?php while($k = $kelasList->fetch_assoc()): ?>
                                <option value="<?= (int)$k['id'] ?>" <?= ((int)$data['id_kelas']==(int)$k['id']?'selected':'') ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><b>Password Baru (opsional)</b></label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan bila tidak ingin mengubah password">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><b>Foto Profil</b></label><br>
                        <img src="../uploads/siswa/<?= htmlspecialchars($data['foto']) ?>"
                             style="width:100px;height:100px;object-fit:cover;border-radius:8px;">
                        <input type="file" name="foto" class="form-control mt-2" accept="image/*">
                        <div class="form-text">Jika tidak diubah, biarkan kosong.</div>
                    </div>

                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="siswa.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

<?php include 'layout/footer.php'; ?>

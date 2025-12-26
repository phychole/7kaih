<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

// Jika submit (PROSES DULU SEBELUM INCLUDE LAYOUT)
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $nisn   = trim($_POST['nisn'] ?? '');
    $nama   = trim($_POST['nama'] ?? '');
    $jk     = $_POST['jk'] ?? 'L';
    $agama  = $_POST['agama'] ?? '';
    $kelas  = (int)($_POST['id_kelas'] ?? 0);
    $passRaw = $_POST['password'] ?? '';

    if ($nisn === '' || $nama === '' || $kelas <= 0 || $passRaw === '') {
        $error = "Mohon lengkapi data yang wajib diisi.";
    } else {

        $pass = password_hash($passRaw, PASSWORD_DEFAULT);

        // cek nisn duplikat
        $cek = $conn->prepare("SELECT id FROM siswa WHERE nisn=? LIMIT 1");
        $cek->bind_param("s", $nisn);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $error = "NISN sudah digunakan.";
        } else {

            // upload foto
            $fotoName = "default.png";

            if (!empty($_FILES['foto']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allow = ['jpg','jpeg','png','webp'];

                if (!in_array($ext, $allow)) {
                    $error = "Format foto harus jpg/jpeg/png/webp.";
                } else {
                    $folder = "../uploads/siswa/";
                    if (!is_dir($folder)) {
                        @mkdir($folder, 0777, true);
                    }

                    $fotoName = "siswa_" . time() . "_" . rand(1000,9999) . "." . $ext;
                    $dest = $folder . $fotoName;

                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
                        $error = "Upload foto gagal.";
                    }
                }
            }

            if ($error === "") {
                $stmt = $conn->prepare("
                    INSERT INTO siswa (nisn,nama,jenis_kelamin,agama,id_kelas,password,foto)
                    VALUES (?,?,?,?,?,?,?)
                ");
                $stmt->bind_param("ssssiss",
                    $nisn, $nama, $jk, $agama, $kelas, $pass, $fotoName
                );
                $stmt->execute();

                header("Location: siswa.php?msg=add");
                exit;
            }
        }
    }
}

// kelas list (untuk form)
$kelasList = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");

// BARU INCLUDE LAYOUT
include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="content">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="fas fa-user-plus"></i> Tambah Siswa</h3>
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
            <input type="text" name="nisn" class="form-control" required value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label"><b>Nama</b></label>
            <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label"><b>Jenis Kelamin</b></label>
            <select name="jk" class="form-select">
              <option value="L" <?= (($_POST['jk'] ?? 'L')=='L'?'selected':'') ?>>Laki-Laki</option>
              <option value="P" <?= (($_POST['jk'] ?? '')=='P'?'selected':'') ?>>Perempuan</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label"><b>Agama</b></label>
            <select name="agama" class="form-select">
              <?php
              $agamaOpt = ["Islam","Kristen","Katolik","Hindu","Budha","Konghucu"];
              $valAg = $_POST['agama'] ?? '';
              foreach($agamaOpt as $a){
                  $sel = ($valAg === $a) ? 'selected' : '';
                  echo "<option $sel>".htmlspecialchars($a)."</option>";
              }
              ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label"><b>Kelas</b></label>
            <select name="id_kelas" class="form-select" required>
              <option value="">-- Pilih Kelas --</option>
              <?php while($k = $kelasList->fetch_assoc()): ?>
                <option value="<?= (int)$k['id'] ?>" <?= (($_POST['id_kelas'] ?? '') == $k['id'] ? 'selected' : '') ?>>
                  <?= htmlspecialchars($k['nama_kelas']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label"><b>Password</b></label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label"><b>Foto Profil</b></label>
            <input type="file" name="foto" class="form-control" accept="image/*">
            <div class="form-text">Jika tidak diisi, foto default akan digunakan.</div>
          </div>

        </div>

        <div class="mt-4">
          <button class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
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

<?php
include 'layout/header.php';
include 'layout/sidebar.php';
if ($_SESSION['role'] != 'admin') exit;
?>

<div class="content">

<h3><i class="fa fa-upload"></i> Import Data Kelas (CSV)</h3>

<div class="alert alert-info">
Format CSV dipisah dengan <b>titik koma ( ; )</b> seperti:<br>
<code>nama_kelas;id_guru_wali</code><br>
<small class="text-muted">
Kolom <b>id_guru_wali</b> boleh kosong. Jika diisi, harus sesuai dengan kolom <b>id</b> pada tabel <b>guru</b>.
</small>
</div>

<form action="kelas_import_proses.php" method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Pilih File CSV</label>
    <input type="file" name="file_csv" class="form-control" accept=".csv" required>
  </div>

  <button class="btn btn-primary">
    <i class="fa fa-upload"></i> Import
  </button>
  <a href="kelas.php" class="btn btn-secondary">Kembali</a>
</form>

</div>

<?php include 'layout/footer.php'; ?>

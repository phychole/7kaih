<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<div class="content">

<h3 class="mb-4"><i class="fas fa-upload"></i> Import Data Siswa (CSV)</h3>

<div class="alert alert-info">
Format CSV:<br>
<code>nisn,nama,jenis_kelamin,agama,id_kelas,password</code>
</div>

<div class="card shadow">
<div class="card-body">

<form action="siswa_import_proses.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label><b>Pilih File CSV</b></label>
        <input type="file" name="file_csv" class="form-control" accept=".csv" required>
    </div>

    <button class="btn btn-primary">
        <i class="fas fa-upload"></i> Import
    </button>
    <a href="siswa.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</form>

</div>
</div>

</div>

<?php include 'layout/footer.php'; ?>

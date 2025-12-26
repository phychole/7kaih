<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<div class="content">

<h3 class="mb-3"><i class="fa fa-upload"></i> Import Data Guru (CSV)</h3>

<div class="alert alert-info">
    <b>Gunakan pemisah (delimiter) titik koma <code>;</code> (bukan koma).</b><br>
    Format header wajib:<br>
    <code>nama;email;jenis_kelamin;agama;password</code><br><br>
    Contoh baris:<br>
    <code>Budi Santoso;budi@mail.com;L;Islam;123456</code>
</div>

<form action="guru_import_proses.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Pilih File CSV</label>
        <input type="file" name="file_csv" class="form-control" accept=".csv" required>
        <small class="text-muted">Tips: saat simpan dari Excel pilih CSV, lalu pastikan delimiter diset <b>;</b></small>
    </div>

    <button class="btn btn-primary">
        <i class="fa fa-upload"></i> Import
    </button>

    <a href="guru.php" class="btn btn-secondary">Kembali</a>
</form>

</div>

<?php include 'layout/footer.php'; ?>

<?php 
include 'layout/header.php'; 
include 'layout/sidebar.php'; 

if ($_SESSION['role'] != 'admin') exit;

// Ambil data kelas + nama wali (jika ada)
$sql = "
    SELECT k.*, g.nama AS wali_nama
    FROM kelas k
    LEFT JOIN guru g ON k.id_guru_wali = g.id
    ORDER BY k.nama_kelas ASC
";
$kelas = $conn->query($sql);
?>

<div class="content">

    <h3 class="mb-4">
        <i class="fas fa-school"></i> Data Kelas
    </h3>
<?php if (isset($_GET['error']) && $_GET['error']=='msh_ada_siswa'): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> 
    Kelas tidak dapat dihapus karena masih ada <b><?= $_GET['jumlah'] ?></b> siswa yang menggunakan kelas ini.
</div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> Kelas berhasil dihapus.
</div>
<?php endif; ?>

   <div class="mb-3 text-end">
  <a href="kelas_import.php" class="btn btn-primary">
    <i class="fas fa-upload"></i> Import Kelas (CSV)
  </a>
  <a href="kelas_tambah.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Tambah Kelas
  </a>
</div>


    <div class="card shadow">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Kelas</th>
                            <th>Wali Kelas</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($kelas->num_rows == 0): ?>
                            <tr>
                                <td colspan="3" class="text-center text-danger">
                                    Belum ada data kelas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while($k = $kelas->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($k['nama_kelas']) ?></td>
                                <td>
                                    <?php if (!empty($k['id_guru_wali']) && !empty($k['wali_nama'])): ?>
                                        <?= htmlspecialchars($k['wali_nama']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ditentukan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="kelas_edit.php?id=<?= $k['id'] ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="kelas_hapus.php?id=<?= $k['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus kelas ini? Data siswa tetap ada, tapi tidak terkait kelas ini.')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<?php include 'layout/footer.php'; ?>

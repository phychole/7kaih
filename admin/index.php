<?php 
// Panggil layout header + sidebar
include 'layout/header.php'; 
include 'layout/sidebar.php'; 
?>

<div class="content">

    <h2 class="mb-4"><i class="fas fa-house"></i> Dashboard Admin</h2>
    <p>Selamat datang, <b><?= $_SESSION['nama'] ?></b> 👋</p>

    <div class="row mt-4">

        <!-- CARD GURU -->
        <div class="col-md-4 mb-3">
            <div class="card shadow card-menu border-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chalkboard-user"></i> Data Guru</h5>
                    <p>Kelola semua data guru.</p>
                    <a href="guru.php" class="btn btn-primary"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

        <!-- CARD SISWA -->
        <div class="col-md-4 mb-3">
            <div class="card shadow card-menu border-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-graduate"></i> Data Siswa</h5>
                    <p>Kelola data siswa lengkap.</p>
                    <a href="siswa.php" class="btn btn-success"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

        <!-- CARD KELAS -->
        <div class="col-md-4 mb-3">
            <div class="card shadow card-menu border-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-school"></i> Data Kelas</h5>
                    <p>Kelola daftar kelas.</p>
                    <a href="kelas.php" class="btn btn-warning"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

    </div>


    <!-- MASTER KEGIATAN -->
    <h4 class="mt-5"><i class="fas fa-gear"></i> Master Kegiatan</h4>
    <div class="row mt-3">

        <div class="col-md-3 mb-3">
            <div class="card shadow card-menu border-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-dumbbell"></i> Olahraga</h5>
                    <p>Daftar jenis olahraga.</p>
                    <a href="olahraga.php" class="btn btn-info text-white"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow card-menu border-secondary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-book-open"></i> Belajar</h5>
                    <p>Daftar jenis belajar.</p>
                    <a href="belajar.php" class="btn btn-secondary text-white"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow card-menu border-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-carrot"></i> Makanan Sehat</h5>
                    <p>Daftar makanan sehat.</p>
                    <a href="makanan.php" class="btn btn-success"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow card-menu border-danger">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-people-group"></i> Bermasyarakat</h5>
                    <p>Daftar kegiatan masyarakat.</p>
                    <a href="masyarakat.php" class="btn btn-danger"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

    </div>


    <!-- MASTER 7 KEBIASAAN -->
    <h4 class="mt-5"><i class="fas fa-list-check"></i> Master 7 Kebiasaan</h4>
    <div class="row mt-3">

        <div class="col-md-6 mb-3">
            <div class="card shadow card-menu border-dark">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-list-ul"></i> Kegiatan Utama</h5>
                    <p>Kelola 7 kebiasaan utama.</p>
                    <a href="kegiatan.php" class="btn btn-dark"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow card-menu border-dark">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-align-left"></i> Detail Kegiatan</h5>
                    <p>Kelola detail/deskripsi kegiatan.</p>
                    <a href="detail_kegiatan.php" class="btn btn-dark"><i class="fa fa-edit"></i> Kelola</a>
                </div>
            </div>
        </div>

    </div>


    <!-- LAPORAN -->
    <h4 class="mt-5"><i class="fas fa-chart-pie"></i> Laporan</h4>
    <div class="row mt-3">

        <div class="col-md-4 mb-4">
            <div class="card shadow card-menu border-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-lines"></i> Rekap Jurnal</h5>
                    <p>Lihat laporan rekap siswa.</p>
                    <a href="rekap.php" class="btn btn-primary"><i class="fa fa-eye"></i> Lihat</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

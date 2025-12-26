<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="content">

    <h3><i class="fas fa-home"></i> Dashboard Guru</h3>

    <p>Selamat datang, <b><?= $_SESSION['nama'] ?></b> 👋</p>

    <div class="row mt-4">

        <div class="col-md-4">
            <div class="card shadow border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary"></i>
                    <h5 class="mt-3">Wali Kelas</h5>
                    <a href="wali_kelas.php" class="btn btn-primary mt-2">Lihat Siswa</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-success">
                <div class="card-body text-center">
                    <i class="fas fa-user-friends fa-3x text-success"></i>
                    <h5 class="mt-3">Guru Wali</h5>
                    <a href="pewalian_list.php" class="btn btn-success mt-2">Lihat Binaan</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-key fa-3x text-warning"></i>
                    <h5 class="mt-3">Ganti Password</h5>
                    <a href="ganti_password.php" class="btn btn-warning mt-2">Ubah Password</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

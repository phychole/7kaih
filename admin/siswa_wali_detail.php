<?php
require '../config.php';
require '../auth.php';

if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_guru = (int)($_GET['id_guru'] ?? 0);
if ($id_guru <= 0) {
    header("Location: siswa_wali.php");
    exit;
}

// ambil guru
$stmt = $conn->prepare("SELECT id, nama, nip, email FROM guru WHERE id=?");
$stmt->bind_param("i", $id_guru);
$stmt->execute();
$guru = $stmt->get_result()->fetch_assoc();

if (!$guru) {
    header("Location: siswa_wali.php");
    exit;
}

// ambil siswa yang diwali guru ini
$stmt2 = $conn->prepare("
    SELECT 
        s.id,
        s.nama,
        s.nisn,
        k.nama_kelas
    FROM guru_wali_siswa gw
    JOIN siswa s ON gw.id_siswa = s.id
    JOIN kelas k ON s.id_kelas = k.id
    WHERE gw.id_guru = ?
    ORDER BY k.nama_kelas ASC, s.nama ASC
");
$stmt2->bind_param("i", $id_guru);
$stmt2->execute();
$list = $stmt2->get_result();

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0"><i class="fas fa-users"></i> Detail Siswa Wali</h3>
            <div class="text-muted">
                Guru: <b><?= htmlspecialchars($guru['nama']) ?></b>
                <?php if (!empty($guru['nip'])): ?> — NIP: <b><?= htmlspecialchars($guru['nip']) ?></b><?php endif; ?>
            </div>
        </div>

        <a href="siswa_wali.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div><i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($guru['email'] ?? '-') ?></div>
            <div class="mt-2">
                <span class="badge bg-primary">Total: <?= (int)$list->num_rows ?> siswa</span>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="60">No</th>
                    <th>Nama Siswa</th>
                    <th width="160">NISN</th>
                    <th width="200">Kelas</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($list->num_rows == 0): ?>
                <tr>
                    <td colspan="4" class="text-center text-danger">Belum ada siswa wali.</td>
                </tr>
            <?php endif; ?>

            <?php $no=1; while($s = $list->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($s['nama']) ?></td>
                    <td><?= htmlspecialchars($s['nisn']) ?></td>
                    <td><?= htmlspecialchars($s['nama_kelas']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layout/footer.php'; ?>

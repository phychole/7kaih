<?php
require '../config.php';
require '../auth.php';

if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: ../login.php");
    exit;
}

include 'layout/header.php';
include 'layout/sidebar.php';

$q = trim($_GET['q'] ?? '');

$sql = "
    SELECT 
        g.id,
        g.nama,
        g.nip,
        g.email,
        COUNT(gw.id_siswa) AS total_walian
    FROM guru g
    LEFT JOIN guru_wali_siswa gw ON gw.id_guru = g.id
    WHERE 1=1
";

$params = [];
$types  = "";

if ($q !== '') {
    $sql .= " AND (g.nama LIKE ? OR g.nip LIKE ? OR g.email LIKE ?) ";
    $like = "%{$q}%";
    $params = [$like, $like, $like];
    $types  = "sss";
}

$sql .= " GROUP BY g.id, g.nama, g.nip, g.email ";
$sql .= " ORDER BY total_walian DESC, g.nama ASC ";

$stmt = $conn->prepare($sql);
if ($types !== "") $stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result();
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0"><i class="fas fa-user-friends"></i> Guru Wali Siswa</h3>
            <div class="text-muted">Daftar semua guru dan jumlah siswa yang diwali.</div>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control"
                           placeholder="Cari nama / NIP / email guru..."
                           value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="siswa_wali.php" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="60">No</th>
                    <th>Nama Guru</th>
                    <th width="160">NIP</th>
                    <th width="220">Email</th>
                    <th width="160" class="text-center">Jumlah Siswa Wali</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($data->num_rows == 0): ?>
                <tr>
                    <td colspan="6" class="text-center text-danger">Data tidak ditemukan.</td>
                </tr>
            <?php endif; ?>

            <?php $no=1; while($g = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($g['nama']) ?></td>
                    <td><?= htmlspecialchars($g['nip'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($g['email'] ?? '-') ?></td>
                    <td class="text-center">
                        <span class="badge bg-primary"><?= (int)$g['total_walian'] ?></span>
                    </td>
                    <td>
                        <a class="btn btn-info btn-sm"
                           href="siswa_wali_detail.php?id_guru=<?= (int)$g['id'] ?>">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layout/footer.php'; ?>

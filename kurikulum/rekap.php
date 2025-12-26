<?php
include 'layout/header.php';
include 'layout/sidebar.php';

// FILTER
$filter_kelas = $_GET['id_kelas'] ?? '';
$filter_nama  = $_GET['nama'] ?? '';

// PAGING
$limit = 10;
$page  = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Ambil kelas untuk dropdown
$kelasRes = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");

/* =========================================
   WHERE untuk filter (dipakai 2x: count & data)
========================================= */
$where = " WHERE 1=1 ";
if ($filter_kelas !== '') {
    $where .= " AND s.id_kelas = " . intval($filter_kelas) . " ";
}
if ($filter_nama !== '') {
    $safe_nama = $conn->real_escape_string($filter_nama);
    $where .= " AND s.nama LIKE '%$safe_nama%' ";
}

/* =========================================
   HITUNG TOTAL DATA (untuk total pages)
========================================= */
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM siswa s
    JOIN kelas k ON s.id_kelas = k.id
    $where
";
$totalRows = (int)($conn->query($sqlCount)->fetch_assoc()['total'] ?? 0);
$totalPages = (int)ceil($totalRows / $limit);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;

/* =========================================
   AMBIL DATA + TOTAL ISIAN JURNAL PER SISWA
========================================= */
$sql = "
    SELECT 
        s.id AS id_siswa,
        s.nama,
        s.nisn,
        k.nama_kelas,
        COUNT(j.id) AS total_jurnal
    FROM siswa s
    JOIN kelas k ON s.id_kelas = k.id
    LEFT JOIN jurnal_siswa j ON j.id_siswa = s.id
    $where
    GROUP BY s.id, s.nama, s.nisn, k.nama_kelas
    ORDER BY k.nama_kelas ASC, s.nama ASC
    LIMIT $limit OFFSET $offset
";
$data = $conn->query($sql);

// helper buat querystring pagination (biar filter kebawa)
function buildQuery($params) {
    return http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
}

$baseParams = [
    'id_kelas' => $filter_kelas,
    'nama'     => $filter_nama
];
?>

<div class="content">

    <h3 class="mb-3">
        <i class="fas fa-users"></i> Rekap Siswa Per Kelas
    </h3>

    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>

    <!-- FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">

                <div class="col-md-4">
                    <label class="form-label"><b>Kelas</b></label>
                    <select name="id_kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        <?php while($k = $kelasRes->fetch_assoc()): ?>
                            <option value="<?= (int)$k['id'] ?>"
                                <?= $filter_kelas == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><b>Nama Siswa</b></label>
                    <input type="text" name="nama" class="form-control"
                           placeholder="Cari siswa..."
                           value="<?= htmlspecialchars($filter_nama) ?>">
                </div>

                <div class="col-md-4 text-end align-self-end">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                    <a href="rekap.php" class="btn btn-secondary">
                        Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- INFO TOTAL -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-muted">
            Total data: <b><?= $totalRows ?></b> siswa
        </div>
        <div class="text-muted">
            Halaman <b><?= $page ?></b> / <b><?= $totalPages ?></b>
        </div>
    </div>

    <!-- TABEL -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:60px;">No</th>
                    <th>Kelas</th>
                    <th>Nama</th>
                    <th>NISN</th>
                    <th style="width:140px;">Total Jurnal</th>
                    <th style="width:170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($data->num_rows == 0): ?>
                <tr>
                    <td colspan="6" class="text-center text-danger">Tidak ada data.</td>
                </tr>
            <?php endif; ?>

            <?php
            $no = $offset + 1;
            while($d = $data->fetch_assoc()):
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($d['nama_kelas']) ?></td>
                    <td><?= htmlspecialchars($d['nama']) ?></td>
                    <td><?= htmlspecialchars($d['nisn']) ?></td>
                    <td class="text-center">
                        <span class="badge bg-primary"><?= (int)$d['total_jurnal'] ?></span>
                    </td>
                    <td>
                        <a href="lihat_jurnal.php?id_siswa=<?= (int)$d['id_siswa'] ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat Jurnal
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination justify-content-center">

        <?php
        $prevPage = $page - 1;
        $nextPage = $page + 1;

        $qPrev = buildQuery(array_merge($baseParams, ['page' => $prevPage]));
        $qNext = buildQuery(array_merge($baseParams, ['page' => $nextPage]));

        // range tampilan nomor halaman (biar tidak kepanjangan)
        $range = 2;
        $start = max(1, $page - $range);
        $end   = min($totalPages, $page + $range);
        ?>

        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="rekap.php?<?= $qPrev ?>">« Prev</a>
        </li>

        <?php if ($start > 1): ?>
          <li class="page-item"><a class="page-link" href="rekap.php?<?= buildQuery(array_merge($baseParams, ['page'=>1])) ?>">1</a></li>
          <?php if ($start > 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="rekap.php?<?= buildQuery(array_merge($baseParams, ['page'=>$i])) ?>">
              <?= $i ?>
            </a>
          </li>
        <?php endfor; ?>

        <?php if ($end < $totalPages): ?>
          <?php if ($end < $totalPages - 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
          <li class="page-item"><a class="page-link" href="rekap.php?<?= buildQuery(array_merge($baseParams, ['page'=>$totalPages])) ?>"><?= $totalPages ?></a></li>
        <?php endif; ?>

        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
          <a class="page-link" href="rekap.php?<?= $qNext ?>">Next »</a>
        </li>

      </ul>
    </nav>
    <?php endif; ?>

</div>

<?php include 'layout/footer.php'; ?>

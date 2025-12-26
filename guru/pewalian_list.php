<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') exit;

include 'layout/header.php';
include 'layout/sidebar.php';

date_default_timezone_set('Asia/Jakarta');

$id_guru = (int)($_SESSION['user_id'] ?? 0);
$today   = date('Y-m-d');

// icon helper
function iconDone($ok){
  return $ok
    ? '<span class="text-success fw-bold">✓</span>'
    : '<span class="text-danger fw-bold">✗</span>';
}

/*
  Ambil siswa wali + ringkasan jurnal hari ini per kegiatan
  - cnt1..cnt7 = jumlah entri per kegiatan hari ini
  - ibadah_dist = jumlah ibadah unik (Islam: sholat unik) / non: jenis ibadah unik
  - makan_dist  = jumlah waktu_makan unik (pagi/siang/malam)
*/
$sql = "
  SELECT
    gw.id AS id_pewalian,
    s.id  AS id_siswa,
    s.nama,
    s.nisn,
    s.agama,
    k.nama_kelas,

    SUM(CASE WHEN j.id_kegiatan=1 THEN 1 ELSE 0 END) AS cnt1,
    SUM(CASE WHEN j.id_kegiatan=2 THEN 1 ELSE 0 END) AS cnt2,
    SUM(CASE WHEN j.id_kegiatan=3 THEN 1 ELSE 0 END) AS cnt3,
    SUM(CASE WHEN j.id_kegiatan=4 THEN 1 ELSE 0 END) AS cnt4,
    SUM(CASE WHEN j.id_kegiatan=5 THEN 1 ELSE 0 END) AS cnt5,
    SUM(CASE WHEN j.id_kegiatan=6 THEN 1 ELSE 0 END) AS cnt6,
    SUM(CASE WHEN j.id_kegiatan=7 THEN 1 ELSE 0 END) AS cnt7,

    COUNT(DISTINCT CASE
      WHEN j.id_kegiatan=2 AND j.ibadah IS NOT NULL AND j.ibadah<>'' THEN
        CASE
          WHEN LOWER(j.ibadah)='zuhur' THEN 'dzuhur'
          WHEN LOWER(j.ibadah)='asar'  THEN 'ashar'
          ELSE LOWER(j.ibadah)
        END
    END) AS ibadah_dist,

    COUNT(DISTINCT CASE
      WHEN j.id_kegiatan=4 AND j.waktu_makan IS NOT NULL AND j.waktu_makan<>'' THEN LOWER(j.waktu_makan)
    END) AS makan_dist

  FROM guru_wali_siswa gw
  JOIN siswa s ON gw.id_siswa = s.id
  JOIN kelas k ON s.id_kelas = k.id

  LEFT JOIN jurnal_siswa j
    ON j.id_siswa = s.id
   AND DATE(j.tanggal) = ?   /* aman jika tanggal DATETIME */

  WHERE gw.id_guru = ?
  GROUP BY
    gw.id, s.id, s.nama, s.nisn, s.agama, k.nama_kelas
  ORDER BY s.nama ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) exit("Query gagal: " . $conn->error);

$stmt->bind_param("si", $today, $id_guru);
$stmt->execute();
$data = $stmt->get_result();
?>

<div class="content">

  <h3><i class="fas fa-user-friends"></i> Data Siswa Wali</h3>
  <p class="text-muted mb-3">Daftar semua siswa binaan Anda sebagai guru wali.</p>

  <div class="d-flex justify-content-between mb-3">
    <a href="pewalian_tambah.php" class="btn btn-primary">
      <i class="fas fa-user-plus"></i> Tambah Siswa Wali
    </a>

    <a href="index.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr class="text-center">
          <th rowspan="2" style="width:50px;">No</th>
          <th rowspan="2">Nama</th>
          <th rowspan="2">NISN</th>
          <th rowspan="2" style="width:120px;">Agama</th>
          <th rowspan="2" style="width:140px;">Kelas</th>

          <th colspan="7">Kegiatan Hari Ini</th>

          <th rowspan="2" style="width:110px;">Ibadah</th>
          <th rowspan="2" style="width:110px;">Makan</th>
          <th rowspan="2" style="width:180px;">Aksi</th>
        </tr>
        <tr class="text-center">
          <th style="width:85px;">Bangun</th>
          <th style="width:85px;">Ibadah</th>
          <th style="width:85px;">Olahraga</th>
          <th style="width:85px;">Makan</th>
          <th style="width:85px;">Belajar</th>
          <th style="width:95px;">Masyarakat</th>
          <th style="width:85px;">Tidur</th>
        </tr>
      </thead>

      <tbody>
      <?php if ($data->num_rows == 0): ?>
        <tr>
          <td colspan="16" class="text-center text-danger">Belum ada siswa wali.</td>
        </tr>
      <?php endif; ?>

      <?php $no=1; while($s = $data->fetch_assoc()):
        $agama = strtolower(trim($s['agama'] ?? ''));

        $cnt1 = (int)$s['cnt1'];
        $cnt2 = (int)$s['cnt2'];
        $cnt3 = (int)$s['cnt3'];
        $cnt4 = (int)$s['cnt4'];
        $cnt5 = (int)$s['cnt5'];
        $cnt6 = (int)$s['cnt6'];
        $cnt7 = (int)$s['cnt7'];

        $ibd = (int)$s['ibadah_dist'];
        $mkn = (int)$s['makan_dist'];

        $ibadahText = ($agama === 'islam') ? ($ibd . "/5") : (string)$ibd;
        $makanText  = $mkn . "/3";

        // warna badge ibadah
        if ($cnt2 > 0) {
          if ($agama === 'islam') $ibColor = ($ibd >= 5 ? 'success' : 'warning');
          else                   $ibColor = ($ibd >= 1 ? 'success' : 'danger');
        }

        // warna badge makan
        $mkColor = ($cnt4 > 0) ? ($mkn >= 3 ? 'success' : 'warning') : 'danger';
      ?>
        <tr class="text-center">
          <td><?= $no++ ?></td>
          <td class="text-start"><?= htmlspecialchars($s['nama']) ?></td>
          <td class="text-start"><?= htmlspecialchars($s['nisn']) ?></td>
          <td><?= htmlspecialchars($s['agama'] ?? '-') ?></td>
          <td><?= htmlspecialchars($s['nama_kelas']) ?></td>

          <td><?= iconDone($cnt1 > 0) ?></td>
          <td><?= iconDone($cnt2 > 0) ?></td>
          <td><?= iconDone($cnt3 > 0) ?></td>
          <td><?= iconDone($cnt4 > 0) ?></td>
          <td><?= iconDone($cnt5 > 0) ?></td>
          <td><?= iconDone($cnt6 > 0) ?></td>
          <td><?= iconDone($cnt7 > 0) ?></td>

          <!-- Ibadah jumlah -->
          <td>
            <?php if ($cnt2 > 0): ?>
              <span class="badge bg-<?= $ibColor ?>"><?= htmlspecialchars($ibadahText) ?></span>
            <?php else: ?>
              <span class="badge bg-danger">0</span>
            <?php endif; ?>
          </td>

          <!-- Makan jumlah -->
          <td>
            <?php if ($cnt4 > 0): ?>
              <span class="badge bg-<?= $mkColor ?>"><?= htmlspecialchars($makanText) ?></span>
            <?php else: ?>
              <span class="badge bg-danger">0/3</span>
            <?php endif; ?>
          </td>

          <td class="text-start">
            <a href="lihat_jurnal.php?id_siswa=<?= (int)$s['id_siswa'] ?>" class="btn btn-info btn-sm">
              <i class="fas fa-book-reader"></i> Jurnal
            </a>

            <a href="hapus_pewalian.php?id=<?= (int)$s['id_pewalian'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Hapus siswa dari daftar wali Anda?')">
              <i class="fas fa-trash"></i> Hapus
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<?php include 'layout/footer.php'; ?>

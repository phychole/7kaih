<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') exit;

include 'layout/header.php';
include 'layout/sidebar.php';

date_default_timezone_set('Asia/Jakarta');

$id_guru = (int)($_SESSION['user_id'] ?? 0);
$today   = date('Y-m-d');

// cek wali kelas
$stmt = $conn->prepare("SELECT id, nama_kelas FROM kelas WHERE id_guru_wali = ? LIMIT 1");
$stmt->bind_param("i", $id_guru);
$stmt->execute();
$kelas = $stmt->get_result()->fetch_assoc();
?>

<div class="content">
<h3><i class="fas fa-users"></i> Wali Kelas</h3>

<?php if (!$kelas): ?>

  <div class="alert alert-warning mt-3">
    <i class="fas fa-exclamation-triangle"></i> Anda <b>bukan wali kelas</b>.
  </div>

<?php else: ?>

  <div class="alert alert-info">
    Anda adalah wali kelas: <b><?= htmlspecialchars($kelas['nama_kelas']) ?></b>
  </div>

  <?php
  $id_kelas = (int)$kelas['id'];

  // Ambil data siswa + ringkasan jurnal hari ini per kegiatan
  // - cnt1..cnt7 = jumlah entri per kegiatan hari ini
  // - ibadah_dist = jumlah sholat unik (Islam) / jumlah ibadah unik (non)
  // - makan_dist = jumlah waktu_makan unik (Pagi/Siang/Malam)
  $sql = "
    SELECT
      s.id, s.nama, s.nisn, s.agama,

      SUM(CASE WHEN j.id_kegiatan=1 THEN 1 ELSE 0 END) AS cnt1,
      SUM(CASE WHEN j.id_kegiatan=2 THEN 1 ELSE 0 END) AS cnt2,
      SUM(CASE WHEN j.id_kegiatan=3 THEN 1 ELSE 0 END) AS cnt3,
      SUM(CASE WHEN j.id_kegiatan=4 THEN 1 ELSE 0 END) AS cnt4,
      SUM(CASE WHEN j.id_kegiatan=5 THEN 1 ELSE 0 END) AS cnt5,
      SUM(CASE WHEN j.id_kegiatan=6 THEN 1 ELSE 0 END) AS cnt6,
      SUM(CASE WHEN j.id_kegiatan=7 THEN 1 ELSE 0 END) AS cnt7,

      COUNT(DISTINCT CASE WHEN j.id_kegiatan=2 AND j.ibadah IS NOT NULL AND j.ibadah<>'' THEN LOWER(j.ibadah) END) AS ibadah_dist,
      COUNT(DISTINCT CASE WHEN j.id_kegiatan=4 AND j.waktu_makan IS NOT NULL AND j.waktu_makan<>'' THEN LOWER(j.waktu_makan) END) AS makan_dist

    FROM siswa s
    LEFT JOIN jurnal_siswa j
      ON j.id_siswa = s.id
     AND j.tanggal = ?
    WHERE s.id_kelas = ?
    GROUP BY s.id, s.nama, s.nisn, s.agama
    ORDER BY s.nama ASC
  ";
  $qs = $conn->prepare($sql);
  $qs->bind_param("si", $today, $id_kelas);
  $qs->execute();
  $siswa = $qs->get_result();

  function iconDone($ok){
    return $ok
      ? '<span class="text-success fw-bold">✓</span>'
      : '<span class="text-danger fw-bold">✗</span>';
  }
  ?>

  <div class="table-responsive mt-3">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr class="text-center">
          <th rowspan="2" style="width:50px;">No</th>
          <th rowspan="2">Nama</th>
          <th rowspan="2">NISN</th>
          <th rowspan="2" style="width:120px;">Agama</th>
          <th colspan="7">Kegiatan Hari Ini</th>
          <th rowspan="2" style="width:110px;">Ibadah</th>
          <th rowspan="2" style="width:110px;">Makan</th>
          <th rowspan="2" style="width:120px;">Aksi</th>
        </tr>
        <tr class="text-center">
          <th style="width:85px;">Bangun</th>
          <th style="width:85px;">Ibadah</th>
          <th style="width:85px;">Olahraga</th>
          <th style="width:85px;">Makan</th>
          <th style="width:85px;">Belajar</th>
          <th style="width:85px;">Masyarakat</th>
          <th style="width:85px;">Tidur</th>
        </tr>
      </thead>
      <tbody>

      <?php if ($siswa->num_rows == 0): ?>
        <tr>
          <td colspan="15" class="text-center text-danger">Tidak ada siswa di kelas ini.</td>
        </tr>
      <?php endif; ?>

      <?php $no=1; while($s = $siswa->fetch_assoc()): 
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

        // status ibadah: Islam target 5, non-Islam target minimal 1 (tapi kita tampilkan jumlahnya)
        $ibadahText = ($agama === 'islam') ? ($ibd . "/5") : (string)$ibd;

        // status makan: target 3
        $makanText  = $mkn . "/3";
      ?>
        <tr class="text-center">
          <td><?= $no++ ?></td>
          <td class="text-start"><?= htmlspecialchars($s['nama']) ?></td>
          <td class="text-start"><?= htmlspecialchars($s['nisn']) ?></td>
          <td><?= htmlspecialchars($s['agama'] ?? '-') ?></td>

          <td><?= iconDone($cnt1 > 0) ?></td>
          <td><?= iconDone($cnt2 > 0) ?></td>
          <td><?= iconDone($cnt3 > 0) ?></td>
          <td><?= iconDone($cnt4 > 0) ?></td>
          <td><?= iconDone($cnt5 > 0) ?></td>
          <td><?= iconDone($cnt6 > 0) ?></td>
          <td><?= iconDone($cnt7 > 0) ?></td>

          <!-- jumlah khusus -->
          <td>
            <?php if ($cnt2 > 0): ?>
              <span class="badge bg-<?= ($agama === 'islam' ? ($ibd >= 5 ? 'success' : 'warning') : ($ibd >= 1 ? 'success' : 'danger')) ?>">
                <?= htmlspecialchars($ibadahText) ?>
              </span>
            <?php else: ?>
              <span class="badge bg-danger">0</span>
            <?php endif; ?>
          </td>

          <td>
            <?php if ($cnt4 > 0): ?>
              <span class="badge bg-<?= ($mkn >= 3 ? 'success' : 'warning') ?>">
                <?= htmlspecialchars($makanText) ?>
              </span>
            <?php else: ?>
              <span class="badge bg-danger">0/3</span>
            <?php endif; ?>
          </td>

          <td>
            <a href="lihat_jurnal.php?id_siswa=<?= (int)$s['id'] ?>" class="btn btn-info btn-sm">
              <i class="fas fa-book-reader"></i> Jurnal
            </a>
          </td>
        </tr>
      <?php endwhile; ?>

      </tbody>
    </table>
  </div>

<?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>

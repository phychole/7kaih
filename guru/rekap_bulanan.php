<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') exit;

include 'layout/header.php';
include 'layout/sidebar.php';

date_default_timezone_set('Asia/Jakarta');

$id_guru = (int)($_SESSION['user_id'] ?? 0);

// cek wali kelas
$stmt = $conn->prepare("SELECT id, nama_kelas FROM kelas WHERE id_guru_wali = ? LIMIT 1");
$stmt->bind_param("i", $id_guru);
$stmt->execute();
$kelas = $stmt->get_result()->fetch_assoc();

$year  = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) $month = (int)date('n');

$bulanNama = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$judulBulan = ($bulanNama[$month] ?? '') . " " . $year;

?>
<div class="content">
  <h3><i class="fas fa-calendar-alt"></i> Rekap Bulanan (Wali Kelas)</h3>

  <?php if (!$kelas): ?>
    <div class="alert alert-warning mt-3">
      <i class="fas fa-exclamation-triangle"></i> Anda <b>bukan wali kelas</b>.
    </div>
  <?php else: ?>

    <div class="alert alert-info">
      Kelas: <b><?= htmlspecialchars($kelas['nama_kelas']) ?></b> |
      Periode: <b><?= htmlspecialchars($judulBulan) ?></b>
    </div>

    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
          <div class="col-md-2">
            <label class="form-label"><b>Tahun</b></label>
            <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2020" max="2100">
          </div>

          <div class="col-md-3">
            <label class="form-label"><b>Bulan</b></label>
            <select name="month" class="form-select">
              <?php
              foreach($bulanNama as $i=>$nm){
                $sel = ($i == $month) ? 'selected' : '';
                echo "<option value='$i' $sel>$nm</option>";
              }
              ?>
            </select>
          </div>

          <div class="col-md-7 text-end">
            <button class="btn btn-primary">
              <i class="fas fa-search"></i> Tampilkan
            </button>

            <a target="_blank"
               class="btn btn-danger"
               href="rekap_bulanan_pdf.php?year=<?= $year ?>&month=<?= $month ?>">
              <i class="fas fa-file-pdf"></i> Download PDF
            </a>
          </div>
        </form>
      </div>
    </div>

    <div class="alert alert-secondary">
      Rekap bulanan menampilkan <b>jumlah hari</b> siswa mengisi jurnal tiap kegiatan.
      <br>
      <b>Ibadah:</b> Islam lengkap jika 5 waktu/hari; non-Islam minimal 1/hari.
      <b>Makan:</b> lengkap jika Pagi+Siang+Malam.
    </div>

    <?php
      $id_kelas = (int)$kelas['id'];
      $start = sprintf("%04d-%02d-01", $year, $month);
      $end   = date("Y-m-t", strtotime($start)); // last day month

      $sql = "
      SELECT
        s.id,
        s.nama,
        s.nisn,
        s.agama,

        COALESCE(SUM(d.bangun),0)     AS hari_bangun,
        COALESCE(SUM(d.ibadah_any),0) AS hari_ibadah_any,
        COALESCE(SUM(d.olahraga),0)   AS hari_olahraga,
        COALESCE(SUM(d.makan_any),0)  AS hari_makan_any,
        COALESCE(SUM(d.belajar),0)    AS hari_belajar,
        COALESCE(SUM(d.masyarakat),0) AS hari_masyarakat,
        COALESCE(SUM(d.tidur),0)      AS hari_tidur,

        COALESCE(SUM(
          CASE
            WHEN LOWER(TRIM(s.agama))='islam' THEN (d.ibadah_cnt >= 5)
            ELSE (d.ibadah_cnt >= 1)
          END
        ),0) AS hari_ibadah_lengkap,

        COALESCE(SUM(d.makan_cnt >= 3),0) AS hari_makan_lengkap

      FROM siswa s
      LEFT JOIN (
        SELECT
          j.id_siswa,
          DATE(j.tanggal) AS tgl,

          MAX(j.id_kegiatan=1) AS bangun,
          MAX(j.id_kegiatan=3) AS olahraga,
          MAX(j.id_kegiatan=5) AS belajar,
          MAX(j.id_kegiatan=6) AS masyarakat,
          MAX(j.id_kegiatan=7) AS tidur,

          MAX(j.id_kegiatan=2) AS ibadah_any,
          COUNT(DISTINCT CASE
            WHEN j.id_kegiatan=2 AND j.ibadah IS NOT NULL AND j.ibadah<>'' THEN
              CASE
                WHEN LOWER(j.ibadah)='zuhur' THEN 'dzuhur'
                WHEN LOWER(j.ibadah)='asar'  THEN 'ashar'
                ELSE LOWER(j.ibadah)
              END
          END) AS ibadah_cnt,

          MAX(j.id_kegiatan=4) AS makan_any,
          COUNT(DISTINCT CASE
            WHEN j.id_kegiatan=4 AND j.waktu_makan IS NOT NULL AND j.waktu_makan<>'' THEN LOWER(j.waktu_makan)
          END) AS makan_cnt

        FROM jurnal_siswa j
        WHERE DATE(j.tanggal) BETWEEN ? AND ?
        GROUP BY j.id_siswa, DATE(j.tanggal)
      ) d ON d.id_siswa = s.id
      WHERE s.id_kelas = ?
      GROUP BY s.id, s.nama, s.nisn, s.agama
      ORDER BY s.nama ASC
      ";

      $q = $conn->prepare($sql);
      $q->bind_param("ssi", $start, $end, $id_kelas);
      $q->execute();
      $data = $q->get_result();
    ?>

    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th rowspan="2" style="width:50px;">No</th>
            <th rowspan="2">Nama</th>
            <th rowspan="2" style="width:110px;">NISN</th>
            <th rowspan="2" style="width:110px;">Agama</th>
            <th colspan="7">Jumlah Hari Terisi</th>
            <th rowspan="2" style="width:140px;">Ibadah<br><small>lengkap / ada</small></th>
            <th rowspan="2" style="width:140px;">Makan<br><small>lengkap / ada</small></th>
          </tr>
          <tr>
            <th>Bangun</th>
            <th>Ibadah</th>
            <th>Olahraga</th>
            <th>Makan</th>
            <th>Belajar</th>
            <th>Masyarakat</th>
            <th>Tidur</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($data->num_rows == 0): ?>
            <tr>
              <td colspan="13" class="text-center text-danger">Tidak ada data pada periode ini.</td>
            </tr>
          <?php endif; ?>

          <?php $no=1; while($s = $data->fetch_assoc()): 
            $ib_lengkap = (int)$s['hari_ibadah_lengkap'];
            $ib_ada     = (int)$s['hari_ibadah_any'];
            $mk_lengkap = (int)$s['hari_makan_lengkap'];
            $mk_ada     = (int)$s['hari_makan_any'];
            $agamaLower = strtolower(trim($s['agama'] ?? ''));
          ?>
            <tr class="text-center">
              <td><?= $no++ ?></td>
              <td class="text-start"><?= htmlspecialchars($s['nama']) ?></td>
              <td><?= htmlspecialchars($s['nisn']) ?></td>
              <td><?= htmlspecialchars($s['agama'] ?? '-') ?></td>

              <td><?= (int)$s['hari_bangun'] ?></td>
              <td><?= (int)$s['hari_ibadah_any'] ?></td>
              <td><?= (int)$s['hari_olahraga'] ?></td>
              <td><?= (int)$s['hari_makan_any'] ?></td>
              <td><?= (int)$s['hari_belajar'] ?></td>
              <td><?= (int)$s['hari_masyarakat'] ?></td>
              <td><?= (int)$s['hari_tidur'] ?></td>

              <td>
                <?php if ($agamaLower === 'islam'): ?>
                  <span class="badge bg-<?= ($ib_lengkap > 0 ? 'success' : 'warning') ?>">
                    <?= $ib_lengkap ?> / <?= $ib_ada ?>
                  </span>
                <?php else: ?>
                  <span class="badge bg-<?= ($ib_lengkap > 0 ? 'success' : 'warning') ?>">
                    <?= $ib_lengkap ?> / <?= $ib_ada ?>
                  </span>
                <?php endif; ?>
              </td>

              <td>
                <span class="badge bg-<?= ($mk_lengkap > 0 ? 'success' : 'warning') ?>">
                  <?= $mk_lengkap ?> / <?= $mk_ada ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>

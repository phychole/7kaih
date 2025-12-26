<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$id_kegiatan = (int)($_GET['id_kegiatan'] ?? 0);
if ($id_kegiatan <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil info kegiatan
$stmt = $conn->prepare("
    SELECT jk.id, jk.nama_kegiatan, dk.deskripsi 
    FROM jenis_kegiatan jk
    LEFT JOIN detail_kegiatan dk ON jk.id = dk.id_kegiatan
    WHERE jk.id=?
    LIMIT 1
");
$stmt->bind_param("i", $id_kegiatan);
$stmt->execute();
$k = $stmt->get_result()->fetch_assoc();
if (!$k) {
    header("Location: dashboard.php");
    exit;
}

// Ambil agama
$id_siswa = (int)($_SESSION['user_id'] ?? 0);
$stAg = $conn->prepare("SELECT agama FROM siswa WHERE id=? LIMIT 1");
$stAg->bind_param("i", $id_siswa);
$stAg->execute();
$agama = $stAg->get_result()->fetch_assoc()['agama'] ?? '';
$agamaLower = strtolower(trim($agama));

$today = date('Y-m-d');

// Master data
$olahragaList   = $conn->query("SELECT * FROM jenis_olahraga ORDER BY nama ASC");
$belajarList    = $conn->query("SELECT * FROM jenis_belajar ORDER BY nama ASC");
$makananList    = $conn->query("SELECT * FROM jenis_makanan ORDER BY nama ASC");
$masyarakatList = $conn->query("SELECT * FROM jenis_masyarakat ORDER BY nama ASC");

// =========================
// MODE EDIT (jika ada)
// =========================
$isEdit = false;
$editId = (int)($_GET['id_jurnal'] ?? 0);
$existing = null;

if (!empty($_GET['edit']) && $editId > 0) {
    $q = $conn->prepare("SELECT * FROM jurnal_siswa WHERE id=? AND id_siswa=? LIMIT 1");
    $q->bind_param("ii", $editId, $id_siswa);
    $q->execute();
    $existing = $q->get_result()->fetch_assoc();
    if ($existing) {
        $isEdit = true;
        // pastikan kegiatan sama
        if ((int)$existing['id_kegiatan'] !== $id_kegiatan || $existing['tanggal'] !== $today) {
            // edit hanya untuk hari ini & kegiatan yg sama (biar sederhana)
            $isEdit = false;
            $existing = null;
        }
    }
}

// =========================
// Untuk disable pilihan yg sudah diisi (hari ini)
// =========================
$filledIbadah = []; // [subuh=>true,...]
$filledMakan  = []; // [pagi=>true, siang=>true, malam=>true]

if ($id_kegiatan == 2 && $agamaLower === 'islam') {
    $st = $conn->prepare("
        SELECT ibadah FROM jurnal_siswa
        WHERE id_siswa=? AND tanggal=? AND id_kegiatan=2
          AND ibadah IS NOT NULL AND ibadah <> ''
    ");
    $st->bind_param("is", $id_siswa, $today);
    $st->execute();
    $r = $st->get_result();
    while ($row = $r->fetch_assoc()) {
        $val = strtolower(trim($row['ibadah']));
        if ($val === 'zuhur') $val = 'dzuhur';
        if ($val === 'asar') $val = 'ashar';
        $filledIbadah[$val] = true;
    }

    // kalau sedang edit, maka pilihan yang diedit tidak dianggap "terisi" untuk disable
    if ($isEdit && !empty($existing['ibadah'])) {
        $val = strtolower(trim($existing['ibadah']));
        if ($val === 'zuhur') $val = 'dzuhur';
        if ($val === 'asar') $val = 'ashar';
        unset($filledIbadah[$val]);
    }
}

if ($id_kegiatan == 4) {
    $st = $conn->prepare("
        SELECT waktu_makan FROM jurnal_siswa
        WHERE id_siswa=? AND tanggal=? AND id_kegiatan=4
          AND waktu_makan IS NOT NULL AND waktu_makan <> ''
    ");
    $st->bind_param("is", $id_siswa, $today);
    $st->execute();
    $r = $st->get_result();
    while ($row = $r->fetch_assoc()) {
        $val = strtolower(trim($row['waktu_makan'])); // pagi/siang/malam
        $filledMakan[$val] = true;
    }

    if ($isEdit && !empty($existing['waktu_makan'])) {
        $val = strtolower(trim($existing['waktu_makan']));
        unset($filledMakan[$val]);
    }
}

// helper untuk checkbox makanan: dari string csv menjadi array
$existingMakananArr = [];
if ($isEdit && !empty($existing['makanan_sehat'])) {
    $existingMakananArr = array_map('trim', explode(',', $existing['makanan_sehat']));
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Isi Jurnal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  body { background: #f8f9fb; }
  .container { max-width: 640px; }
  .checkbox-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: 6px 10px; }
  @media(min-width: 576px){ .checkbox-grid { grid-template-columns: repeat(2, 1fr); } }
  @media(min-width: 768px){ .checkbox-grid { grid-template-columns: repeat(3, 1fr); } }
  .card-desc { font-size: 0.95rem; background: #eef6ff; padding: 10px 15px; border-left: 4px solid #0d6efd; border-radius: 6px; }
</style>

<script>
function showOther(selectID, boxID) {
  let select = document.getElementById(selectID);
  let box    = document.getElementById(boxID);
  box.style.display = (select.value === 'Lain-lain') ? 'block' : 'none';
  if (select.value !== "Lain-lain") {
    box.querySelector("input").value = "";
  }
}
</script>

</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand"><?= htmlspecialchars($k['nama_kegiatan']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container mt-4">

  <h4 class="fw-bold"><?= htmlspecialchars($k['nama_kegiatan']) ?></h4>

  <?php if ($isEdit): ?>
    <div class="alert alert-warning">
      Kamu sedang <b>mengedit</b> jurnal hari ini. Setelah disimpan, data lama akan berubah.
    </div>
  <?php endif; ?>

  <?php if (!empty($k['deskripsi'])): ?>
    <div class="card-desc mb-3">
      <?= nl2br(htmlspecialchars($k['deskripsi'])) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= $isEdit ? 'update_jurnal.php' : 'simpan_jurnal.php' ?>">
    <input type="hidden" name="id_kegiatan" value="<?= (int)$k['id'] ?>">

    <?php if ($isEdit): ?>
      <input type="hidden" name="id_jurnal" value="<?= (int)$existing['id'] ?>">
    <?php endif; ?>

    <!-- TANGGAL -->
    <div class="mb-3">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($today) ?>" readonly>
    </div>

    <?php if ($id_kegiatan == 1): ?>
      <!-- 🌅 BANGUN PAGI -->
      <div class="mb-3">
        <label class="form-label">Jam Bangun Pagi</label>
        <input type="time" name="jam_bangun" class="form-control"
               value="<?= htmlspecialchars($existing['jam_bangun'] ?? '') ?>" required>
      </div>

    <?php elseif ($id_kegiatan == 2): ?>
      <!-- 🙏 IBADAH -->
      <?php if ($agamaLower == 'islam'): ?>
        <div class="mb-3">
          <label class="form-label">Pilih Sholat</label>
          <select name="ibadah" class="form-control" required>
            <?php
              $opsi = ['Subuh','Dzuhur','Ashar','Magrib','Isya'];
              $cur  = $existing['ibadah'] ?? '';
              foreach ($opsi as $o):
                $key = strtolower($o);
                $disabled = !empty($filledIbadah[$key]) ? 'disabled' : '';
                $selected = ($cur === $o) ? 'selected' : '';
            ?>
              <option value="<?= $o ?>" <?= $selected ?> <?= $disabled ?>><?= $o ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Sholat yang sudah diisi hari ini akan terkunci.</div>
        </div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Jenis Ibadah</label>
          <input type="text" name="ibadah" class="form-control"
                 value="<?= htmlspecialchars($existing['ibadah'] ?? '') ?>" required>
        </div>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Jam Pelaksanaan</label>
        <input type="time" name="jam_ibadah" class="form-control"
               value="<?= htmlspecialchars($existing['jam_ibadah'] ?? '') ?>" required>
      </div>

    <?php elseif ($id_kegiatan == 3): ?>
      <!-- 🏃‍♂️ OLAHRAGA -->
      <div class="mb-3">
        <label class="form-label">Jenis Olahraga</label>
        <select name="olahraga" id="olahragaSelect" class="form-control"
                onchange="showOther('olahragaSelect','olahragaLain')" required>
          <?php
          $cur = $existing['olahraga'] ?? '';
          while($o = $olahragaList->fetch_assoc()):
            $sel = ($cur === $o['nama']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($o['nama']) ?>" <?= $sel ?>><?= htmlspecialchars($o['nama']) ?></option>
          <?php endwhile; ?>
          <option value="Lain-lain">Lain-lain</option>
        </select>
      </div>

      <div class="mb-3" id="olahragaLain" style="display:none;">
        <label class="form-label">Isi jenis olahraga lain</label>
        <input type="text" name="olahraga_lain" class="form-control">
      </div>

    <?php elseif ($id_kegiatan == 4): ?>
      <!-- 🍽 MAKAN SEHAT -->
      <div class="mb-2">
        <label class="form-label">Waktu Makan</label>
        <select name="waktu_makan" class="form-control" required>
          <?php
            $cur = $existing['waktu_makan'] ?? '';
            $opsi = [
              'Pagi'  => 'Sarapan',
              'Siang' => 'Makan Siang',
              'Malam' => 'Makan Malam',
            ];
            foreach ($opsi as $val => $label):
              $key = strtolower($val);
              $disabled = !empty($filledMakan[$key]) ? 'disabled' : '';
              $selected = ($cur === $val) ? 'selected' : '';
          ?>
            <option value="<?= $val ?>" <?= $selected ?> <?= $disabled ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Waktu makan yang sudah diisi hari ini akan terkunci.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Makanan / Minuman Sehat</label>
        <div class="checkbox-grid">
          <?php while($m = $makananList->fetch_assoc()): 
            $checked = in_array($m['nama'], $existingMakananArr) ? 'checked' : '';
          ?>
            <label>
              <input type="checkbox" name="makanan_sehat[]" value="<?= htmlspecialchars($m['nama']) ?>" <?= $checked ?>>
              <?= htmlspecialchars($m['nama']) ?>
            </label>
          <?php endwhile; ?>
        </div>

        <input type="text" name="makanan_lain"
               class="form-control mt-2" placeholder="Lain-lain (opsional)">
      </div>

    <?php elseif ($id_kegiatan == 5): ?>
      <!-- 📚 BELAJAR -->
      <div class="mb-3">
        <label class="form-label">Jenis Kegiatan Belajar</label>
        <select name="belajar" id="belajarSelect" class="form-control"
                onchange="showOther('belajarSelect','belajarLain')" required>
          <?php
          $cur = $existing['belajar'] ?? '';
          while($b = $belajarList->fetch_assoc()):
            $sel = ($cur === $b['nama']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($b['nama']) ?>" <?= $sel ?>><?= htmlspecialchars($b['nama']) ?></option>
          <?php endwhile; ?>
          <option value="Lain-lain">Lain-lain</option>
        </select>
      </div>

      <div class="mb-3" id="belajarLain" style="display:none;">
        <label class="form-label">Isi jenis belajar lain</label>
        <input type="text" name="belajar_lain" class="form-control">
      </div>

    <?php elseif ($id_kegiatan == 6): ?>
      <!-- 👥 BERMASYARAKAT -->
      <div class="mb-3">
        <label class="form-label">Kegiatan Bermasyarakat</label>
        <select name="masyarakat" id="masyarakatSelect" class="form-control"
                onchange="showOther('masyarakatSelect','masyarakatLain')" required>
          <?php
          $cur = $existing['masyarakat'] ?? '';
          while($ms = $masyarakatList->fetch_assoc()):
            $sel = ($cur === $ms['nama']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($ms['nama']) ?>" <?= $sel ?>><?= htmlspecialchars($ms['nama']) ?></option>
          <?php endwhile; ?>
          <option value="Lain-lain">Lain-lain</option>
        </select>
      </div>

      <div class="mb-3" id="masyarakatLain" style="display:none;">
        <label class="form-label">Isi kegiatan lain</label>
        <input type="text" name="masyarakat_lain" class="form-control">
      </div>

    <?php elseif ($id_kegiatan == 7): ?>
      <!-- 🌙 TIDUR CEPAT -->
      <div class="mb-3">
        <label class="form-label">Jam Tidur</label>
        <input type="time" name="jam_tidur" class="form-control"
               value="<?= htmlspecialchars($existing['jam_tidur'] ?? '') ?>" required>
      </div>

    <?php endif; ?>

    <!-- CATATAN -->
    <div class="mb-3">
      <label class="form-label">Catatan (opsional)</label>
      <textarea name="catatan" class="form-control"><?= htmlspecialchars($existing['catatan'] ?? '') ?></textarea>
    </div>

    <!-- STATUS -->
    <div class="mb-3">
      <label class="form-label">Status</label>
      <?php $curNilai = $existing['nilai'] ?? 'Belum'; ?>
      <select name="nilai" class="form-control">
        <option value="Belum" <?= ($curNilai === 'Belum') ? 'selected' : '' ?>>Belum</option>
        <option value="Sudah" <?= ($curNilai === 'Sudah') ? 'selected' : '' ?>>Sudah</option>
      </select>
    </div>

    <button class="btn btn-primary w-100"><?= $isEdit ? 'Update' : 'Simpan' ?></button>
    <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Kembali</a>

  </form>
</div>

</body>
</html>

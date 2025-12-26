<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_siswa = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

// Ambil data jurnal
$stmt = $conn->prepare("
    SELECT j.*, jk.nama_kegiatan, s.agama 
    FROM jurnal_siswa j
    JOIN jenis_kegiatan jk ON j.id_kegiatan = jk.id
    JOIN siswa s ON s.id = j.id_siswa
    WHERE j.id=? AND j.id_siswa=?
");
$stmt->bind_param("ii", $id, $id_siswa);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    exit("Data tidak ditemukan atau bukan milik Anda.");
}

$id_kegiatan = $data['id_kegiatan'];
$agamaSiswa  = $data['agama'];

// Ambil master data
$olahragaList   = $conn->query("SELECT nama FROM jenis_olahraga ORDER BY nama ASC");
$belajarList    = $conn->query("SELECT nama FROM jenis_belajar ORDER BY nama ASC");
$makananList    = $conn->query("SELECT nama FROM jenis_makanan ORDER BY nama ASC");
$masyarakatList = $conn->query("SELECT nama FROM jenis_masyarakat ORDER BY nama ASC");

// FIX deprecated explode(): jika null → jadi string kosong
$makanan_string = $data['makanan_sehat'] ?? "";
$terpilih_makanan = $makanan_string !== "" ? array_map('trim', explode(",", $makanan_string)) : [];
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Jurnal - <?=htmlspecialchars($data['nama_kegiatan'])?></title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f4f6f9; }
.container { max-width: 850px; }

.checkbox-grid {
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap:6px 12px;
}

/* Mobile grid */
@media(max-width:576px){
    .checkbox-grid {
        grid-template-columns: repeat(2, 1fr);
        gap:6px 10px;
    }
}

/* Hide class */
.hidden { display:none; }
</style>

</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <span class="navbar-brand">Edit Jurnal</span>
    <a href="riwayat.php" class="btn btn-outline-light btn-sm">Kembali</a>
  </div>
</nav>

<div class="container">

<h4 class="mb-3">Edit Jurnal: <?=htmlspecialchars($data['nama_kegiatan'])?></h4>

<form method="POST" action="update_jurnal.php">
<input type="hidden" name="id" value="<?=$data['id']?>">

<!-- TANGGAL -->
<div class="mb-3">
  <label class="form-label">Tanggal</label>
  <input type="date" value="<?=$data['tanggal']?>" class="form-control" readonly>
</div>


<?php if ($id_kegiatan == 1): ?>
<!-- 🌅 BANGUN PAGI -->
<div class="mb-3">
    <label class="form-label">Jam Bangun Pagi</label>
    <input type="time" name="jam_bangun" class="form-control" value="<?=$data['jam_bangun']?>" required>
</div>

<?php elseif ($id_kegiatan == 2): ?>
<!-- 🙏 IBADAH -->
<?php if (strtolower($agamaSiswa) == 'islam'): ?>
<div class="mb-3">
    <label class="form-label">Pilih Sholat</label>
    <select name="ibadah" class="form-select" required>
        <?php foreach(["Subuh","Dzuhur","Ashar","Magrib","Isya"] as $s): ?>
            <option value="<?=$s?>" <?=$data['ibadah']==$s?'selected':''?>><?=$s?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php else: ?>
<div class="mb-3">
    <label class="form-label">Jenis Ibadah</label>
    <input type="text" name="ibadah" class="form-control" value="<?=htmlspecialchars($data['ibadah'])?>" required>
</div>
<?php endif; ?>

<div class="mb-3">
    <label class="form-label">Jam Pelaksanaan</label>
    <input type="time" name="jam_ibadah" class="form-control" value="<?=$data['jam_ibadah']?>" required>
</div>

<?php elseif ($id_kegiatan == 3): ?>
<!-- 🏃‍♂️ OLAHRAGA -->
<?php
// cek apakah olahraga lain-lain
$master = [];
while($o = $olahragaList->fetch_assoc()) $master[] = $o['nama'];

$isOther = !in_array($data['olahraga'], $master);
$olahragaList = $conn->query("SELECT nama FROM jenis_olahraga ORDER BY nama ASC");
?>

<div class="mb-3">
    <label class="form-label">Jenis Olahraga</label>
    <select name="olahraga" id="olahraga_select" class="form-select">
        <?php while($o = $olahragaList->fetch_assoc()): ?>
        <option value="<?=$o['nama']?>" <?=$data['olahraga']==$o['nama']?'selected':''?>><?=$o['nama']?></option>
        <?php endwhile; ?>
        <option value="Lain-lain" <?=$isOther?'selected':''?>>Lain-lain</option>
    </select>
</div>

<div class="mb-3 <?= $isOther ? '' : 'hidden' ?>" id="olahraga_lain_div">
    <label class="form-label">Isi jika lain-lain</label>
    <input type="text" name="olahraga_lain" class="form-control" value="<?=($isOther?$data['olahraga']:'')?>">
</div>

<?php elseif ($id_kegiatan == 4): ?>
<!-- 🍽 MAKAN SEHAT -->
<div class="mb-3">
    <label class="form-label">Waktu Makan</label>
    <select name="waktu_makan" class="form-select">
        <option value="Pagi"  <?=$data['waktu_makan']=='Pagi'?'selected':''?>>Sarapan</option>
        <option value="Siang" <?=$data['waktu_makan']=='Siang'?'selected':''?>>Makan Siang</option>
        <option value="Malam" <?=$data['waktu_makan']=='Malam'?'selected':''?>>Makan Malam</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Menu Makanan Sehat</label>

    <div class="checkbox-grid">
        <?php while($m = $makananList->fetch_assoc()): ?>
        <label>
            <input type="checkbox" name="makanan_sehat[]"
                value="<?=$m['nama']?>"
                <?= in_array($m['nama'], $terpilih_makanan) ? 'checked' : '' ?>>
            <?=$m['nama']?>
        </label>
        <?php endwhile; ?>
    </div>

    <?php
    $lain = "";
    foreach ($terpilih_makanan as $t) {
        $cek = $conn->query(
            "SELECT COUNT(*) AS j FROM jenis_makanan WHERE nama='".$conn->real_escape_string($t)."'"
        )->fetch_assoc();
        if ($cek['j'] == 0) $lain = $t;
    }
    ?>

    <input type="text" name="makanan_lain" class="form-control mt-2"
           placeholder="Lain-lain (opsional)" value="<?=$lain?>">
</div>

<?php elseif ($id_kegiatan == 5): ?>
<!-- 📚 BELAJAR -->
<?php
$masterBelajar = [];
while($b = $belajarList->fetch_assoc()) $masterBelajar[] = $b['nama'];

$isOther = !in_array($data['belajar'], $masterBelajar);
$belajarList = $conn->query("SELECT nama FROM jenis_belajar ORDER BY nama ASC");
?>

<div class="mb-3">
    <label class="form-label">Jenis Belajar</label>
    <select name="belajar" id="belajar_select" class="form-select">
        <?php while($b = $belajarList->fetch_assoc()): ?>
        <option value="<?=$b['nama']?>" <?=$data['belajar']==$b['nama']?'selected':''?>><?=$b['nama']?></option>
        <?php endwhile; ?>
        <option value="Lain-lain" <?=$isOther?'selected':''?>>Lain-lain</option>
    </select>
</div>

<div class="mb-3 <?= $isOther ? '' : 'hidden' ?>" id="belajar_lain_div">
    <label class="form-label">Isi jika lain-lain</label>
    <input type="text" name="belajar_lain" class="form-control" value="<?=($isOther?$data['belajar']:'')?>">
</div>

<?php elseif ($id_kegiatan == 6): ?>
<!-- 👥 BERMASYARAKAT -->
<?php
$masterMs = [];
while($ms2 = $masyarakatList->fetch_assoc()) $masterMs[] = $ms2['nama'];

$isOther = !in_array($data['masyarakat'], $masterMs);
$masyarakatList = $conn->query("SELECT nama FROM jenis_masyarakat ORDER BY nama ASC");
?>
<div class="mb-3">
    <label class="form-label">Kegiatan Bermasyarakat</label>
    <select name="masyarakat" id="masyarakat_select" class="form-select">
        <?php while($x = $masyarakatList->fetch_assoc()): ?>
        <option value="<?=$x['nama']?>" <?=$data['masyarakat']==$x['nama']?'selected':''?>><?=$x['nama']?></option>
        <?php endwhile; ?>
        <option value="Lain-lain" <?=$isOther?'selected':''?>>Lain-lain</option>
    </select>
</div>

<div class="mb-3 <?= $isOther ? '' : 'hidden' ?>" id="masyarakat_lain_div">
    <label class="form-label">Isi jika lain-lain</label>
    <input type="text" name="masyarakat_lain" class="form-control"
           value="<?=($isOther?$data['masyarakat']:'')?>">
</div>

<?php elseif ($id_kegiatan == 7): ?>
<!-- 🛌 TIDUR -->
<div class="mb-3">
    <label class="form-label">Jam Tidur</label>
    <input type="time" name="jam_tidur" class="form-control" value="<?=$data['jam_tidur']?>" required>
</div>

<?php endif; ?>


<!-- CATATAN -->
<div class="mb-3">
    <label class="form-label">Catatan</label>
    <textarea name="catatan" class="form-control"><?=htmlspecialchars($data['catatan'])?></textarea>
</div>

<!-- STATUS -->
<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="nilai" class="form-select">
        <option value="Sudah" <?=$data['nilai']=='Sudah'?'selected':''?>>Sudah</option>
        <option value="Belum" <?=$data['nilai']=='Belum'?'selected':''?>>Belum</option>
    </select>
</div>

<button class="btn btn-primary w-100">Simpan Perubahan</button>
<a href="riwayat.php" class="btn btn-secondary w-100 mt-2">Kembali</a>

</form>
</div>

<script>
// Toggle bagian "lain-lain"
function toggle(selectId, divId){
    const select = document.getElementById(selectId);
    const div = document.getElementById(divId);
    if (select && div){
        div.classList.toggle('hidden', select.value !== 'Lain-lain');
    }
}

document.getElementById('olahraga_select')?.addEventListener('change', ()=>toggle('olahraga_select','olahraga_lain_div'));
document.getElementById('belajar_select')?.addEventListener('change', ()=>toggle('belajar_select','belajar_lain_div'));
document.getElementById('masyarakat_select')?.addEventListener('change', ()=>toggle('masyarakat_select','masyarakat_lain_div'));
</script>

</body>
</html>

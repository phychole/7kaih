<?php
require '../config.php';
require '../auth.php';

if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: kelas.php");
    exit;
}

/* ==========================
   AMBIL DATA KELAS (SEBELUM OUTPUT)
========================== */
$stmt = $conn->prepare("SELECT * FROM kelas WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$kelas = $stmt->get_result()->fetch_assoc();

if (!$kelas) {
    // boleh output error setelah include juga, tapi ini aman
    $kelasNotFound = true;
} else {
    $kelasNotFound = false;
}

/* ==========================
   PROSES UPDATE (SEBELUM INCLUDE)
========================== */
$error = "";
if (!$kelasNotFound && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_kelas = trim($_POST['nama_kelas'] ?? '');
    $id_guru_wali = $_POST['id_guru_wali'] ?? '';
    $id_guru_wali = ($id_guru_wali === '' ? null : (int)$id_guru_wali);

    if ($nama_kelas === '') {
        $error = "Nama kelas wajib diisi.";
    } else {

        // Jika wali kelas dikosongkan -> set NULL
        if ($id_guru_wali === null) {
            $upd = $conn->prepare("UPDATE kelas SET nama_kelas=?, id_guru_wali=NULL WHERE id=?");
            $upd->bind_param("si", $nama_kelas, $id);
        } else {
            $upd = $conn->prepare("UPDATE kelas SET nama_kelas=?, id_guru_wali=? WHERE id=?");
            $upd->bind_param("sii", $nama_kelas, $id_guru_wali, $id);
        }

        $upd->execute();

        header("Location: kelas.php?msg=updated");
        exit;
    }
}

/* ==========================
   BARU INCLUDE LAYOUT
========================== */
include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="content">

<?php if ($kelasNotFound): ?>
    <div class="alert alert-danger">Kelas tidak ditemukan.</div>
<?php else: ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-edit"></i> Edit Kelas</h3>
        <a href="kelas.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">

                <div class="mb-3">
                    <label class="form-label"><b>Nama Kelas</b></label>
                    <input type="text" name="nama_kelas" class="form-control"
                           value="<?= htmlspecialchars($kelas['nama_kelas']) ?>" required>
                </div>

                <!-- AUTOCOMPLETE -->
                <div class="mb-3 position-relative">
                    <label class="form-label"><b>Wali Kelas (Autocomplete)</b></label>

                    <input type="hidden" name="id_guru_wali" id="id_guru_wali"
                           value="<?= htmlspecialchars($kelas['id_guru_wali'] ?? '') ?>">

                    <?php
                    $namaGuruWali = '';
                    if (!empty($kelas['id_guru_wali'])) {
                        $gid = (int)$kelas['id_guru_wali'];
                        $g = $conn->query("SELECT nama FROM guru WHERE id=$gid")->fetch_assoc();
                        $namaGuruWali = $g['nama'] ?? '';
                    }
                    ?>

                    <input type="text" id="cari_guru" class="form-control"
                           value="<?= htmlspecialchars($namaGuruWali) ?>"
                           placeholder="Ketik nama guru…" autocomplete="off">

                    <div id="hasil_cari"
                         class="list-group position-absolute w-100 shadow"
                         style="z-index:1000; display:none; max-height:260px; overflow:auto;">
                    </div>

                    <div class="form-text">
                        Hapus isi input jika ingin mengosongkan wali kelas.
                    </div>
                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>

                <a href="kelas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

            </form>
        </div>
    </div>

<?php endif; ?>

</div>

<script>
const input = document.getElementById('cari_guru');
const hasil = document.getElementById('hasil_cari');
const hiddenID = document.getElementById('id_guru_wali');

function hideList(){ hasil.style.display = 'none'; }

input?.addEventListener('keyup', function() {
    const q = this.value.trim();

    // jika input dikosongkan -> kosongkan hidden id (set NULL saat submit)
    if (q.length === 0) hiddenID.value = "";

    if (q.length < 2) return hideList();

    fetch('ajax_cari_guru.php?q=' + encodeURIComponent(q))
        .then(res => res.json())
        .then(data => {
            hasil.innerHTML = '';
            if (!data.length) return hideList();

            data.forEach(item => {
                const el = document.createElement('a');
                el.href = "#";
                el.className = "list-group-item list-group-item-action";
                el.innerHTML = `<i class="fas fa-user-tie me-2"></i>${item.nama}`;
                el.addEventListener('click', function(e){
                    e.preventDefault();
                    input.value = item.nama;
                    hiddenID.value = item.id;
                    hideList();
                });
                hasil.appendChild(el);
            });

            hasil.style.display = 'block';
        });
});

document.addEventListener('click', function(e){
    if (!hasil.contains(e.target) && e.target !== input) hideList();
});
</script>

<?php include 'layout/footer.php'; ?>

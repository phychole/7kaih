<?php
require '../config.php';
require '../auth.php';

if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ==========================
   PROSES SIMPAN (SEBELUM INCLUDE)
========================== */
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_kelas = trim($_POST['nama_kelas'] ?? '');
    $id_guru_wali = $_POST['id_guru_wali'] ?? '';
    $id_guru_wali = ($id_guru_wali === '' ? null : (int)$id_guru_wali);

    if ($nama_kelas === '') {
        $error = "Nama kelas wajib diisi.";
    } else {
        // bind_param tidak bisa menerima null langsung untuk "i",
        // jadi kalau NULL -> pakai set NULL via query terpisah / trick.
        if ($id_guru_wali === null) {
            $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, id_guru_wali) VALUES (?, NULL)");
            $stmt->bind_param("s", $nama_kelas);
        } else {
            $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, id_guru_wali) VALUES (?, ?)");
            $stmt->bind_param("si", $nama_kelas, $id_guru_wali);
        }

        $stmt->execute();

        header("Location: kelas.php?msg=add");
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-plus"></i> Tambah Kelas</h3>
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
                    <input type="text" name="nama_kelas" class="form-control" required>
                </div>

                <!-- AUTOCOMPLETE -->
                <div class="mb-3 position-relative">
                    <label class="form-label"><b>Wali Kelas (Autocomplete)</b></label>

                    <input type="hidden" name="id_guru_wali" id="id_guru_wali">

                    <input type="text" id="cari_guru" class="form-control"
                           placeholder="Ketik nama guru…" autocomplete="off">

                    <div id="hasil_cari"
                         class="list-group position-absolute w-100 shadow"
                         style="z-index:1000; display:none; max-height:260px; overflow:auto;">
                    </div>

                    <div class="form-text">
                        Kosongkan jika kelas belum punya wali kelas.
                    </div>
                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>

                <a href="kelas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

            </form>
        </div>
    </div>

</div>

<script>
const input = document.getElementById('cari_guru');
const hasil = document.getElementById('hasil_cari');
const hiddenID = document.getElementById('id_guru_wali');

function hideList(){ hasil.style.display = 'none'; }

input.addEventListener('keyup', function() {
    let q = this.value.trim();

    // kalau input dikosongkan → reset hidden id
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

// klik luar menutup list
document.addEventListener('click', function(e){
    if (!hasil.contains(e.target) && e.target !== input) hideList();
});
</script>

<?php include 'layout/footer.php'; ?>

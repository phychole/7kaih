<?php
include 'layout/header.php';
include 'layout/sidebar.php';

if ($_SESSION['role'] != 'guru') exit;

$id_guru = $_SESSION['user_id'];
?>

<div class="content">

    <h3 class="mb-3">
        <i class="fas fa-user-plus"></i> Tambah Siswa Wali
    </h3>

    <a href="pewalian_list.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST" action="pewalian_simpan.php">

                <input type="hidden" name="id_guru" value="<?= $id_guru ?>">

                <div class="mb-3 position-relative">
                    <label class="form-label"><b>Cari Siswa (Autocomplete)</b></label>
                    <input type="text" id="cari_siswa" class="form-control"
                           placeholder="Ketik nama atau NISN...">

                    <div id="hasil_cari"
                         class="list-group position-absolute w-100 shadow"
                         style="display:none; z-index:2000;">
                    </div>
                </div>

                <h5 class="mt-4">Siswa Dipilih:</h5>
                <div id="selectedSiswa" class="mb-3"></div>

                <input type="hidden" name="selected_ids" id="selected_ids">

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pewalian
                </button>

            </form>

        </div>
    </div>

</div>

<!-- CUSTOM STYLE -->
<style>
.list-group-item { cursor: pointer; }

.selected-tag {
    display: inline-block;
    background: #0d6efd;
    color: white;
    padding: 6px 14px;
    margin: 4px;
    border-radius: 20px;
    font-size: 14px;
    position: relative;
}

.selected-tag i {
    margin-left: 8px;
    cursor: pointer;
}
</style>

<!-- AUTOCOMPLETE SCRIPT -->
<script>
let selected = [];

function updateDisplay() {
    let container = document.getElementById('selectedSiswa');
    let hidden = document.getElementById('selected_ids');

    container.innerHTML = "";

    selected.forEach((s, idx) => {
        let tag = document.createElement("div");
        tag.className = "selected-tag";
        tag.innerHTML = s.nama + " (" + s.kelas + ") <i class='fas fa-times'></i>";

        tag.querySelector("i").onclick = function() {
            selected.splice(idx, 1);
            updateDisplay();
        };

        container.appendChild(tag);
    });

    hidden.value = selected.map(s => s.id).join(",");
}

document.getElementById('cari_siswa').addEventListener('keyup', function() {
    let keyword = this.value.trim();
    let hasil = document.getElementById('hasil_cari');

    if (keyword.length < 2) {
        hasil.style.display = 'none';
        return;
    }

    fetch('ajax_cari_siswa.php?q=' + encodeURIComponent(keyword))
        .then(res => res.json())
        .then(data => {
            hasil.innerHTML = "";

            if (!data.length) {
                hasil.style.display = 'none';
                return;
            }

            data.forEach(item => {
                let el = document.createElement('a');
                el.className = "list-group-item list-group-item-action";
                el.textContent = item.nama + " (" + item.nisn + ") - " + item.kelas;

                el.onclick = function() {
                    // Cegah duplikat siswa
                    if (!selected.find(s => s.id == item.id)) {
                        selected.push(item);
                        updateDisplay();
                    }
                    hasil.style.display = 'none';
                    document.getElementById('cari_siswa').value = "";
                };

                hasil.appendChild(el);
            });

            hasil.style.display = 'block';
        });
});
</script>

<?php include 'layout/footer.php'; ?>

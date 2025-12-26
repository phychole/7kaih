<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'guru') exit;

$id_guru = (int) $_SESSION['user_id'];
$selected = $_POST['selected_ids'] ?? "";

if (trim($selected) == "") {
    header("Location: pewalian_tambah.php?msg=empty");
    exit;
}

// Pecah ID Siswa
$ids = array_filter(array_map('intval', explode(",", $selected)));

if (empty($ids)) {
    header("Location: pewalian_tambah.php?msg=invalid");
    exit;
}

// PREPARED STATEMENTS
$cekStmt = $conn->prepare("SELECT id FROM guru_wali_siswa WHERE id_siswa=?");
$insertStmt = $conn->prepare("INSERT INTO guru_wali_siswa (id_guru, id_siswa) VALUES (?, ?)");

$sukses = 0;
$gagal = 0;

foreach ($ids as $id_siswa) {

    // Validasi id_siswa harus > 0
    if ($id_siswa <= 0) continue;

    // cek sudah punya wali?
    $cekStmt->bind_param("i", $id_siswa);
    $cekStmt->execute();
    $res = $cekStmt->get_result();

    if ($res->num_rows == 0) {

        // Insert pewalian
        $insertStmt->bind_param("ii", $id_guru, $id_siswa);
        if ($insertStmt->execute()) {
            $sukses++;
        } else {
            $gagal++;
        }

    } else {
        // Sudah punya wali → skip
        $gagal++;
    }
}

// Redirect dengan status
header("Location: pewalian_list.php?msg=ok&s=$sukses&g=$gagal");
exit;
?>

<?php
require '../config.php';
require '../auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    header("Location: kelas_import.php");
    exit;
}

$fh = fopen($_FILES['file_csv']['tmp_name'], "r");
if (!$fh) {
    header("Location: kelas_import.php");
    exit;
}

/**
 * CSV delimiter ;
 * Format:
 * nama_kelas;id_guru_wali
 */

$added = 0;
$skipped = 0;

$conn->begin_transaction();

try {
    $headerChecked = false;

    while (($row = fgetcsv($fh, 0, ";")) !== false) {
        if (!$row) continue;

        $row = array_map(fn($v) => trim((string)$v), $row);

        // skip baris kosong
        $allEmpty = true;
        foreach ($row as $v) {
            if ($v !== '') { $allEmpty = false; break; }
        }
        if ($allEmpty) continue;

        $nama_kelas = $row[0] ?? '';
        $wali_raw   = $row[1] ?? '';

        // deteksi header
        if (!$headerChecked) {
            $headerChecked = true;
            $h0 = strtolower($nama_kelas);
            $h1 = strtolower($wali_raw);
            if ($h0 === 'nama_kelas' || $h0 === 'nama kelas' || $h1 === 'id_guru_wali') {
                continue;
            }
        }

        if ($nama_kelas === '') {
            $skipped++;
            continue;
        }

        // cek duplikat nama_kelas
        $cek = $conn->prepare("SELECT id FROM kelas WHERE nama_kelas=? LIMIT 1");
        $cek->bind_param("s", $nama_kelas);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $skipped++;
            continue;
        }

        // validasi id_guru_wali jika diisi
        $id_guru_wali = null;
        if ($wali_raw !== '') {
            $wali_id = (int)$wali_raw;
            if ($wali_id <= 0) {
                $skipped++;
                continue;
            }

            $g = $conn->prepare("SELECT id FROM guru WHERE id=? LIMIT 1");
            $g->bind_param("i", $wali_id);
            $g->execute();
            $gRes = $g->get_result()->fetch_assoc();

            if (!$gRes) {
                // id guru tidak ada
                $skipped++;
                continue;
            }

            $id_guru_wali = $wali_id;
        }

        // insert
        $ins = $conn->prepare("INSERT INTO kelas (nama_kelas, id_guru_wali) VALUES (?, ?)");
        $ins->bind_param("si", $nama_kelas, $id_guru_wali); // null aman untuk kolom nullable
        $ins->execute();

        $added++;
    }

    fclose($fh);
    $conn->commit();

} catch (Throwable $e) {
    if (is_resource($fh)) fclose($fh);
    $conn->rollback();
    header("Location: kelas.php?error=import_gagal");
    exit;
}

header("Location: kelas.php?msg=import&add=$added&skip=$skipped");
exit;

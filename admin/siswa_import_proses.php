<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') exit;

// Biar session tidak ngunci lama saat looping
session_write_close();

// CONFIG
$BATCH_SIZE = 200; // naikkan 1000 kalau server kuat
$TMP_DIR = __DIR__ . '/import_tmp';
$LOG_DIR = __DIR__ . '/import_logs';

if (!is_dir($TMP_DIR)) @mkdir($TMP_DIR, 0755, true);
if (!is_dir($LOG_DIR)) @mkdir($LOG_DIR, 0755, true);

function detectDelimiter($filePath) {
    $sample = file_get_contents($filePath, false, null, 0, 4096);
    if ($sample === false) return ';';
    $lines = preg_split("/\r\n|\n|\r/", $sample);
    $line = $lines[0] ?? '';
    return (substr_count($line, ',') > substr_count($line, ';')) ? ',' : ';';
}

function logLine($fp, $msg) {
    if ($fp) fwrite($fp, "[".date('Y-m-d H:i:s')."] ".$msg.PHP_EOL);
}

$stateFile = $TMP_DIR . '/siswa_import_state_' . session_id() . '.json';

// ====== INIT (request pertama setelah upload) ======
if (!isset($_GET['continue'])) {
    if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
        die("Upload gagal. Kode error: " . ($_FILES['file_csv']['error'] ?? 'unknown'));
    }

    $tmpUpload = $_FILES['file_csv']['tmp_name'];
    if (!file_exists($tmpUpload)) die("File tidak ditemukan.");

    $stored = $TMP_DIR . '/SISWA_' . date('Ymd_His') . '_' . session_id() . '.csv';
    if (!move_uploaded_file($tmpUpload, $stored)) {
        die("Gagal menyimpan file upload.");
    }

    $delimiter = detectDelimiter($stored);

    $logFile = $LOG_DIR . '/siswa_import_' . date('Ymd_His') . '_' . session_id() . '.log';

    $state = [
        'file' => $stored,
        'delimiter' => $delimiter,
        'pos' => 0,
        'first' => true,
        'inserted' => 0,
        'skipped' => 0,
        'lineNo' => 0,
        'log' => $logFile,
        'done' => false,
    ];
    file_put_contents($stateFile, json_encode($state));

    // lanjut ke batch berikutnya
    header("Location: siswa_import_proses.php?continue=1");
    exit;
}

// ====== CONTINUE BATCH ======
if (!file_exists($stateFile)) {
    die("State import tidak ditemukan. Silakan upload ulang.");
}

$state = json_decode(file_get_contents($stateFile), true);
if (!$state || empty($state['file']) || !file_exists($state['file'])) {
    die("File import hilang. Silakan upload ulang.");
}

$logFp = fopen($state['log'], 'a');
logLine($logFp, "Batch start. pos={$state['pos']} inserted={$state['inserted']} skipped={$state['skipped']}");

$csv = fopen($state['file'], 'r');
if (!$csv) die("Gagal membuka file CSV.");

if ($state['pos'] > 0) {
    fseek($csv, $state['pos']);
}

// prepare insert (pakai IGNORE karena nisn UNIQUE)
$stmt = $conn->prepare("
  INSERT IGNORE INTO siswa (nisn,nama,jenis_kelamin,agama,id_kelas,password)
  VALUES (?,?,?,?,?,?)
");
if (!$stmt) die("Prepare insert gagal: " . $conn->error);

// transaksi per-batch biar cepat
$conn->begin_transaction();

$processedThisBatch = 0;
while ($processedThisBatch < $BATCH_SIZE && ($row = fgetcsv($csv, 0, $state['delimiter'], '"', "\\")) !== false) {
    $state['lineNo']++;

    // Skip header sekali
    if ($state['first']) { $state['first'] = false; continue; }

    $processedThisBatch++;

    $row = array_map('trim', $row);
    while (count($row) > 0 && end($row) === '') array_pop($row);

    if (count($row) < 6) {
        $state['skipped']++;
        logLine($logFp, "SKIP line {$state['lineNo']}: kolom kurang (" . count($row) . ") RAW=" . json_encode($row));
        continue;
    }

    list($nisn, $nama, $jk, $agama, $id_kelas, $password) = $row;

    // hapus BOM jika ada
    $nisn = preg_replace('/^\xEF\xBB\xBF/', '', $nisn);

    if ($nisn === '' || $nama === '' || $id_kelas === '') {
        $state['skipped']++;
        logLine($logFp, "SKIP line {$state['lineNo']}: field wajib kosong. NISN='$nisn' NAMA='$nama' KELAS='$id_kelas'");
        continue;
    }

    $jk = strtoupper($jk);
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt->bind_param("ssssss", $nisn, $nama, $jk, $agama, $id_kelas, $pass_hash);
    if (!$stmt->execute()) {
        $state['skipped']++;
        logLine($logFp, "ERROR line {$state['lineNo']}: execute gagal. NISN='$nisn' ERR=" . $stmt->error);
        continue;
    }

    if ($conn->affected_rows > 0) $state['inserted']++;
    else $state['skipped']++; // duplikat (ignored)
}

$conn->commit();

$state['pos'] = ftell($csv);
fclose($csv);
fclose($logFp);

// cek EOF
if (feof(fopen($state['file'], 'r'))) {
    // tidak akurat jika buka ulang; jadi pakai trik: jika barusan fgetcsv() false dan processed < batch => kemungkinan EOF
}
if ($row === false) { // loop berhenti karena EOF / error baca
    $state['done'] = true;
}

file_put_contents($stateFile, json_encode($state));

// ====== FINISH ======
if (!empty($state['done'])) {
    // bersihin state & temp file
    @unlink($stateFile);
    @unlink($state['file']);

    header("Location: siswa.php?msg=import&add={$state['inserted']}&skip={$state['skipped']}&log=" . urlencode(basename($state['log'])));
    exit;
}

// ====== CONTINUE NEXT BATCH ======
// refresh cepat biar lanjut otomatis tanpa JS
header("Location: siswa_import_proses.php?continue=1");
exit;

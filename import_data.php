<?php
require_once 'config/config.php';

$csvFile = __DIR__ . '/data/indexing_final_db.csv'; 

if (!file_exists($csvFile)) {
    die("<h3 style='color:red'>Error: File CSV tidak ditemukan di: $csvFile</h3>");
}

$handle = fopen($csvFile, "r");
$header = fgetcsv($handle);

echo "<div style='font-family: monospace; padding: 20px;'>";
echo "<h2>🚀 Import Data: Perbaikan Mapping & Tanggal</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#eee'><th>No</th><th>File Name (Benar)</th><th>Matkul</th><th>Created At</th><th>Status</th></tr>";

$stmt_ins_sub = $pdo->prepare("INSERT IGNORE INTO subjects (code, name) VALUES (?, ?)");

$stmt_ins_doc = $pdo->prepare("INSERT INTO documents (file_name, subject_code, topic, description, file_path, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

$count = 0;
$rowNum = 0;
$adminId = 1;

while (($row = fgetcsv($handle, 2000, ",")) !== FALSE) {
    $rowNum++;
    if (count($row) < 5) continue;

    $subjectCode = trim($row[0]);
    $subjectName = trim($row[1]);
    $topic       = trim($row[2]); 
    $desc        = trim($row[3]); 
    $fileName    = trim($row[4]);
    
    $createdAt   = !empty($row[6]) ? trim($row[6]) : date('Y-m-d H:i:s');

    $localPath = 'uploads/' . basename($fileName);

    try {
        $stmt_ins_sub->execute([$subjectCode, $subjectName]);
    } catch (Exception $e) {}

    $status = "";
    try {
        $stmt_ins_doc->execute([
            $fileName,
            $subjectCode,
            $topic,
            $desc,
            $localPath,
            $adminId,
            $createdAt
        ]);
        
        $status = "<span style='color:green'>✅ OK</span>";
        $count++;
    } catch (Exception $e) {
        $status = "<span style='color:red'>Gagal: " . $e->getMessage() . "</span>";
    }

    echo "<tr>";
    echo "<td>$rowNum</td>";
    echo "<td>$fileName</td>";
    echo "<td>$subjectCode</td>";
    echo "<td>$createdAt</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

fclose($handle);
echo "</table>";
echo "<br><h3>Selesai! $count data berhasil diperbaiki.</h3>";
echo "<a href='public/index.php'>&rarr; Buka Dashboard</a>";
echo "</div>";
?>

<?php
header('Content-Type: application/json');

session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Akses ditolak. Silakan login ulang.');
    }

    $configPath = __DIR__ . '/../config/config.php';
    
    if (!file_exists($configPath)) {
        throw new Exception("File konfigurasi database tidak ditemukan di: $configPath");
    }
    require_once $configPath;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode request tidak valid.');
    }

    $doc_id = $_POST['doc_id'] ?? '';
    $subject_code = $_POST['subject_code'] ?? '';
    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($doc_id) || empty($subject_code) || empty($topic) || empty($description)) {
        throw new Exception("Semua kolom bertanda * wajib diisi.");
    }

    $stmt = $pdo->prepare("SELECT id, file_path FROM documents WHERE id = ?");
    $stmt->execute([$doc_id]);
    $old_doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$old_doc) {
        throw new Exception("Dokumen tidak ditemukan di database.");
    }

    $final_file_path = $old_doc['file_path'];
    $file_updated = false;
    $new_file_name_display = null;

    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['pdf_file'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            throw new Exception("File harus berformat PDF.");
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception("Ukuran file terlalu besar (Maks 10MB).");
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('modul_', true) . '.' . $extension;
        $destination = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Gagal menyimpan file ke server.");
        }

        $old_physical_path = __DIR__ . '/../' . $old_doc['file_path'];
        if (file_exists($old_physical_path) && is_file($old_physical_path)) {
            unlink($old_physical_path);
        }

        $final_file_path = 'uploads/' . $newFileName;
        $new_file_name_display = $file['name']; 
        $file_updated = true;
    }

    if ($file_updated) {
        $sql = "UPDATE documents SET 
                    subject_code = ?, 
                    topic = ?, 
                    description = ?, 
                    file_path = ?, 
                    file_name = ?
                WHERE id = ?";
        $params = [$subject_code, $topic, $description, $final_file_path, $new_file_name_display, $doc_id];
    } else {
        $sql = "UPDATE documents SET 
                    subject_code = ?, 
                    topic = ?, 
                    description = ?
                WHERE id = ?";
        $params = [$subject_code, $topic, $description, $doc_id];
    }

    $stmtUpdate = $pdo->prepare($sql);
    $exec = $stmtUpdate->execute($params);

    if ($exec) {
        ob_clean(); 
        echo json_encode(['success' => true, 'message' => 'Modul berhasil diperbarui!']);
    } else {
        throw new Exception("Gagal update database.");
    }

} catch (Exception $e) {
    ob_clean(); 
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

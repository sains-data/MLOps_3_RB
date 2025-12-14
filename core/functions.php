<?php
function getSubjectName($pdo, $code) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM subjects WHERE code = ?");
        $stmt->execute([$code]);
        $result = $stmt->fetchColumn();
        
        return $result ? $result : $code;
    } catch (PDOException $e) {
        return $code;
    }
}

function formatDateIndo($dateString) {
    if (empty($dateString)) return '-';
    $date = new DateTime($dateString);
    return $date->format('d-m-Y H:i');
}

function truncateText($text, $limit = 100) {
    if (strlen($text) <= $limit) {
        return $text;
    }
    return substr($text, 0, $limit) . '...';
}
?>

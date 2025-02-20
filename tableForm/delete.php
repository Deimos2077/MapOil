<?php
header('Content-Type: application/json');
require 'db.php';

$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];
$table = isset($_GET['table']) && in_array($_GET['table'], $allowedTables) ? $_GET['table'] : null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (!$table || !$id) {
    echo json_encode(['error' => 'Некорректные параметры.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка БД: ' . $e->getMessage()]);
}
?>

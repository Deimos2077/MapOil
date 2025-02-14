<?php
require 'C:/xampp/htdocs/MapOil/database/db.php';

$table = $_GET['table'];
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];
if (!in_array($table, $allowedTables)) {
    die('Недопустимая таблица.');
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data['id']) {
    // Обновление существующей записи
    $setClause = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
    $stmt = $pdo->prepare("UPDATE $table SET $setClause WHERE id = ?");
    $stmt->execute([...array_values($data), $data['id']]);
} else {
    // Добавление новой записи
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
}
?>

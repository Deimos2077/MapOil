<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем базу данных
require __DIR__ . '/../database/db.php'; // Проверь правильный путь

$data = json_decode(file_get_contents('php://input'), true);
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];

if (!isset($data['table']) || !in_array($data['table'], $allowedTables)) {
    echo json_encode(['error' => '❌ Недопустимая таблица (' . $data['table'] . ')']);
    exit;
}

$table = $_POST['table'] ?? $_GET['table'] ?? '';

if (empty($table)) {
    die(json_encode(["error" => "❌ Не указано имя таблицы"]));
}
$id = isset($data['id']) ? (int) $data['id'] : null;
unset($data['id'], $data['table']);

if (empty($data)) {
    echo json_encode(['error' => '❌ Нет данных для обновления']);
    exit;
}

try {
    if ($id) {
        $updateFields = implode(", ", array_map(fn($col) => "`$col` = :$col", array_keys($data)));
        $sql = "UPDATE `$table` SET $updateFields WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
    }

    foreach ($data as $col => $value) {
        $stmt->bindValue(":$col", $value);
    }

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'id' => $id ?: $pdo->lastInsertId()]);
    } else {
        echo json_encode(['error' => '❌ Данные не изменились']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => '❌ Ошибка БД: ' . $e->getMessage()]);
}
?>

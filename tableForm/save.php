<?php
require '../database/db.php'; // подключаем PDO

header('Content-Type: application/json');

// Получаем входные данные
$data = json_decode(file_get_contents("php://input"), true);

// Проверка таблицы
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];
$table = $data['table'] ?? '';
if (!in_array($table, $allowedTables)) {
    echo json_encode(['success' => false, 'error' => 'Недопустимая таблица.']);
    exit;
}

// Удаляем вспомогательные поля
$id = $data['id'] ?? null;
unset($data['table'], $data['id']);

try {
    if ($id) {
        // 🔄 Обновление существующей записи
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "`$column` = :$column";
        }
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
    } else {
        // ➕ Добавление новой записи
        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($col) => ":$col", array_keys($data)));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

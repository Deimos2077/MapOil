<?php
header('Content-Type: application/json');
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Проверяем, переданы ли обязательные данные
if (!isset($data['id'], $data['table']) || !is_string($data['table'])) {
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

// Разрешенные таблицы
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];

$table = $data['table'];

// Проверяем, разрешена ли таблица
if (!in_array($table, $allowedTables)) {
    echo json_encode(['error' => 'Недопустимая таблица']);
    exit;
}

// Убираем id из данных, чтобы не обновлять его
$id = (int) $data['id'];
unset($data['id']);
unset($data['table']);

if (empty($data)) {
    echo json_encode(['error' => 'Нет данных для обновления']);
    exit;
}

// Формируем SQL-запрос
$columns = array_keys($data);
$updateFields = implode(", ", array_map(fn($col) => "`$col` = :$col", $columns));

$sql = "UPDATE `$table` SET $updateFields WHERE id = :id";
$stmt = $pdo->prepare($sql);

// Привязываем параметры
foreach ($data as $col => $value) {
    $stmt->bindValue(":$col", $value);
}
$stmt->bindValue(":id", $id, PDO::PARAM_INT);

try {
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Нет изменений или запись не найдена']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка БД: ' . $e->getMessage()]);
}
?>

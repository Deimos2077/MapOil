<?php
header('Content-Type: application/json');
include 'db.php';

// Файл логов
$logFile = __DIR__ . '/debug_log.txt';

// Включаем логирование ошибок
ini_set('log_errors', 1);
ini_set('error_log', $logFile);

// Создаем файл логов, если его нет
if (!file_exists($logFile)) {
    file_put_contents($logFile, "=== Лог файл создан: " . date("Y-m-d H:i:s") . " ===\n");
}

// 📌 Читаем JSON-запрос и логируем его
$data = json_decode(file_get_contents('php://input'), true);
error_log("📥 Полученные данные: " . print_r($data, true));

// Проверяем входные данные
if (!isset($data['id'], $data['table']) || !is_string($data['table'])) {
    error_log("❌ Ошибка: Некорректные данные! JSON: " . json_encode($data));
    echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
    exit;
}

// 📌 Разрешенные таблицы
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];
$table = $data['table'];

if (!in_array($table, $allowedTables)) {
    error_log("❌ Ошибка: Попытка обновления недопустимой таблицы: " . $table);
    echo json_encode(['success' => false, 'error' => 'Недопустимая таблица']);
    exit;
}

// 📌 Проверяем ID записи
$id = (int) $data['id'];
if ($id <= 0) {
    error_log("❌ Ошибка: Некорректный ID: " . $id);
    echo json_encode(['success' => false, 'error' => 'Некорректный ID']);
    exit;
}

// 📌 Удаляем `pointId`, если он есть
if (isset($data['pointId'])) {
    error_log("⚠️ Удаляем `pointId`: " . $data['pointId']);
    unset($data['pointId']);
}

// 📌 Удаляем id и table из данных для обновления
unset($data['id'], $data['table']);

if (empty($data)) {
    error_log("⚠ Нет данных для обновления.");
    echo json_encode(['success' => false, 'error' => 'Нет данных для обновления']);
    exit;
}

// 📌 Формируем SQL-запрос
$columns = array_keys($data);
$updateFields = implode(", ", array_map(fn($col) => "`$col` = :$col", $columns));
$sql = "UPDATE `$table` SET $updateFields WHERE id = :id";

// 📌 Логируем SQL-запрос перед выполнением
error_log("🛠 SQL-запрос: " . $sql);
error_log("📝 Данные для обновления: " . print_r($data, true));

// 📌 Подготавливаем запрос
$stmt = $pdo->prepare($sql);

// Привязываем параметры
foreach ($data as $col => $value) {
    $stmt->bindValue(":$col", $value);
}
$stmt->bindValue(":id", $id, PDO::PARAM_INT);

try {
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        error_log("✅ Запись успешно обновлена! ID: $id");
        echo json_encode(['success' => true, 'message' => 'Запись успешно обновлена']);
    } else {
        error_log("⚠ Нет изменений для записи ID: $id");
        echo json_encode(['success' => false, 'error' => 'Нет изменений или запись не обновлена']);
    }
} catch (PDOException $e) {
    error_log("❌ Ошибка БД: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка БД: ' . $e->getMessage()]);
}
?>

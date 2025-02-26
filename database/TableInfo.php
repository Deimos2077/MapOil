<?php
header('Content-Type: application/json');
include 'db.php';

// Логируем входные данные
error_log("\n=== 📥 Вход в TableInfo.php (" . date("Y-m-d H:i:s") . ") ===");

// Проверяем, передан ли `pointId`
$pointId = $_GET['pointId'] ?? null;

if (!$pointId) {
    error_log("❌ Ошибка: `pointId` не передан");
    echo json_encode([]);
    exit;
}

// Приводим `pointId` к числу и логируем
$pointId = (int) $pointId;
error_log("📌 `pointId` перед SQL-запросом: " . $pointId);

try {
    // Подготавливаем SQL-запрос
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.date, p1.name AS from_name, p2.name AS to_name, 
            t.to_amount AS amount, t.losses
        FROM oiltransfer t
        LEFT JOIN Points p1 ON t.from_point_id = p1.id
        LEFT JOIN Points p2 ON t.to_point_id = p2.id
        WHERE t.from_point_id = :pointId OR t.to_point_id = :pointId
    ");

    // Привязываем параметр `pointId`
    $stmt->bindValue(':pointId', $pointId, PDO::PARAM_INT);

    // Логируем SQL перед выполнением
    error_log("🛠 SQL-запрос перед выполнением: SELECT ... WHERE pointId = " . $pointId);

    // Выполняем запрос
    $stmt->execute();

    // Получаем данные
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Логируем результат запроса
    error_log("📤 Результат запроса: " . json_encode($data));

    // Отправляем JSON-ответ
    echo json_encode($data ?: []); // Если данных нет, отправляем пустой массив
} catch (PDOException $e) {
    // Логируем ошибку, если произошел сбой SQL
    error_log("❌ Ошибка SQL: " . $e->getMessage());
    echo json_encode([]);
}
?>

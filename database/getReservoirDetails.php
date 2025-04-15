<?php
require_once 'db.php';

$reservoirId = $_GET['reservoir_id'] ?? null;
$year = $_GET['year'] ?? null;
$month = $_GET['month'] ?? null;

if (!$reservoirId || !$year || !$month) {
    echo json_encode(['error' => 'Недостаточно параметров']);
    exit;
}

$startDate = "$year-$month-01";
$endDate = date("Y-m-t", strtotime($startDate));

// 1. Получаем резервуар
$sqlReservoir = "SELECT * FROM reservoirs WHERE id = ?";
$stmt = $pdo->prepare($sqlReservoir);
$stmt->execute([$reservoirId]);
$reservoir = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservoir) {
    echo json_encode(['error' => 'Резервуар не найден']);
    exit;
}

// 2. Ищем последнюю запись по объёму за указанный месяц
$sqlVolume = "
    SELECT * FROM reservoirvolumes 
    WHERE reservoir_id = :id 
      AND date BETWEEN :start AND :end
      AND (start_volume > 0 OR end_volume > 0)
    ORDER BY date DESC 
    LIMIT 1
";
$stmt = $pdo->prepare($sqlVolume);
$stmt->execute([
    'id' => $reservoirId,
    'start' => $startDate,
    'end' => $endDate
]);
$volume = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'name' => $reservoir['name'],
    'type' => $reservoir['type'],
    'point_id' => $reservoir['point_id'],
    'start_volume' => $volume['start_volume'] ?? 0,
    'end_volume' => $volume['end_volume'] ?? 0,
    'date' => $volume['date'] ?? null
]);

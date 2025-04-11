<?php
require_once 'db.php';

$pointId = $_GET['point_id'];
$year = $_GET['year'];
$month = $_GET['month'];

$startDate = "$year-$month-01";
$endDate = date("Y-m-t", strtotime($startDate));

// 1. Получаем последнюю дату в этом месяце по данной точке
$sql = "
  SELECT MAX(date) as latest_date
  FROM oiltransfer
  WHERE (from_point_id = :point_id OR to_point_id = :point_id)
    AND date BETWEEN :start AND :end
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'start' => $startDate,
  'end' => $endDate
]);
$latestRow = $stmt->fetch(PDO::FETCH_ASSOC);
$latestDate = $latestRow['latest_date'];

if (!$latestDate) {
  echo json_encode([
    'accepted' => 0,
    'transferred' => 0,
    'toPoints' => [],
    'reservoirs' => []
  ]);
  exit;
}

// 2. Принято и передано нефти — только по этой дате
$sql = "
  SELECT
    SUM(CASE WHEN to_point_id = :point_id THEN to_amount ELSE 0 END) AS accepted,
    SUM(CASE WHEN from_point_id = :point_id THEN from_amount ELSE 0 END) AS transferred
  FROM oiltransfer
  WHERE date = :latest AND (from_point_id = :point_id OR to_point_id = :point_id)
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'latest' => $latestDate
]);
$oilSummary = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Куда передано нефть с этой точки — по этой дате
$sql = "
  SELECT p.name, SUM(o.to_amount) as amount
  FROM oiltransfer o
  LEFT JOIN Points p ON o.to_point_id = p.id
  WHERE o.from_point_id = :point_id AND o.date = :latest
  GROUP BY o.to_point_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'latest' => $latestDate
]);
$toPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Резервуары — тоже по этой дате
$sql = "
  SELECT r.name, v.start_volume, v.end_volume
  FROM reservoirvolumes v
  JOIN Reservoirs r ON r.id = v.reservoir_id
  WHERE r.point_id = :point_id
    AND v.date = :latest
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'latest' => $latestDate
]);
$reservoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ответ
echo json_encode([
  'accepted' => $oilSummary['accepted'] ?? 0,
  'transferred' => $oilSummary['transferred'] ?? 0,
  'toPoints' => $toPoints,
  'reservoirs' => $reservoirs
]);

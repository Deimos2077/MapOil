<?php
require_once 'db.php';

$pointId = $_GET['point_id'];
$year = $_GET['year'];
$month = $_GET['month'];

$startDate = "$year-$month-01";
$endDate = date("Y-m-t", strtotime($startDate));

// Принято и передано нефти
$sql = "
  SELECT
    SUM(CASE WHEN to_point_id = :point_id THEN to_amount ELSE 0 END) AS accepted,
    SUM(CASE WHEN from_point_id = :point_id THEN from_amount ELSE 0 END) AS transferred
  FROM oiltransfer
  WHERE date BETWEEN :start AND :end
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'start' => $startDate,
  'end' => $endDate
]);
$oilSummary = $stmt->fetch(PDO::FETCH_ASSOC);

// Куда передано нефть с этой точки
$sql = "
  SELECT p.name, SUM(o.to_amount) as amount
  FROM oiltransfer o
  LEFT JOIN Points p ON o.to_point_id = p.id
  WHERE o.from_point_id = :point_id AND date BETWEEN :start AND :end
  GROUP BY o.to_point_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'start' => $startDate,
  'end' => $endDate
]);
$toPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Резервуары
$sql = "
  SELECT r.name, v.start_volume, v.end_volume
  FROM reservoirvolumes v
  JOIN Reservoirs r ON r.id = v.reservoir_id
  WHERE r.point_id = :point_id
    AND DATE_FORMAT(v.date, '%Y-%m') = :month
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  'point_id' => $pointId,
  'month' => "$year-$month"
]);
$reservoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Ответ
echo json_encode([
  'accepted' => $oilSummary['accepted'] ?? 0,
  'transferred' => $oilSummary['transferred'] ?? 0,
  'toPoints' => $toPoints,
  'reservoirs' => $reservoirs
]);

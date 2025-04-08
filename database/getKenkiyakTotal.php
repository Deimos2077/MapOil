<?php
require_once 'db.php';

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

try {
    $stmt = $pdo->prepare("
        SELECT date, SUM(from_amount) AS total_oil
        FROM oiltransfer
        WHERE from_point_id = 5
          AND YEAR(date) = :year
          AND MONTH(date) = :month
        GROUP BY date
        ORDER BY date DESC
        LIMIT 1
    ");
    $stmt->execute(['year' => $year, 'month' => $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'total_oil' => $result ? (float)$result['total_oil'] : 0
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

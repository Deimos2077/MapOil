<?php
require_once 'db.php';

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

try {
    // Сначала находим последнюю дату в месяце для Кенкияка
    $stmt = $pdo->prepare("
        SELECT MAX(date) as latest_date
        FROM oiltransfer
        WHERE from_point_id = 5
          AND YEAR(date) = :year
          AND MONTH(date) = :month
    ");
    $stmt->execute(['year' => $year, 'month' => $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $latestDate = $result['latest_date'] ?? null;

    if ($latestDate) {
        // Суммируем нефть за эту дату
        $sumStmt = $pdo->prepare("
            SELECT SUM(from_amount) AS total_oil
            FROM oiltransfer
            WHERE from_point_id = 5 AND date = :date
        ");
        $sumStmt->execute(['date' => $latestDate]);
        $sumResult = $sumStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'total_oil' => $sumResult ? (float)$sumResult['total_oil'] : 0,
            'date' => $latestDate
        ]);
    } else {
        echo json_encode([
            'total_oil' => 0,
            'date' => null
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

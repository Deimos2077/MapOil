<?php
include 'db.php'; 

header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

try {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) as total_oil
        FROM oiltransfer
        WHERE to_point_id = 5 
        AND from_point_id IN (12, 11)
        AND YEAR(date) = ? 
        AND MONTH(date) = ?
    ");
    $stmt->execute([$year, $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "year" => $year,
        "month" => $month,
        "total_oil" => $result['total_oil'] ?? 0
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

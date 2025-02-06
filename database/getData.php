<?php
header('Content-Type: application/json');
include 'db.php';

$tableName = $_GET['table'];
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;

try {
    if ($tableName === 'oiltransfer' && $year && $month) {
        $stmt = $pdo->prepare("SELECT * FROM oiltransfer WHERE YEAR(date) = :year AND MONTH(date) = :month");
        $stmt->execute(['year' => $year, 'month' => $month]);
    } elseif ($tableName === 'reservoirvolumes' && $year && $month) {
        $stmt = $pdo->prepare("SELECT * FROM reservoirvolumes WHERE YEAR(date) = :year AND MONTH(date) = :month");
        $stmt->execute(['year' => $year, 'month' => $month]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM $tableName");
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

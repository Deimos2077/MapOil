<?php
header('Content-Type: application/json');
include 'db.php';

$tableName = $_GET['table'];
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;

try {
    if (($tableName === 'oiltransfer' || $tableName === 'reservoirvolumes') && $year && $month) {
        // Получаем последнюю дату за месяц
        $stmt = $pdo->prepare("SELECT MAX(date) as max_date FROM $tableName WHERE YEAR(date) = :year AND MONTH(date) = :month");
        $stmt->execute(['year' => $year, 'month' => $month]);
        $maxDate = $stmt->fetch(PDO::FETCH_ASSOC)['max_date'];

        if ($maxDate) {
            // Получаем записи только за эту дату
            $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE DATE(date) = :maxDate");
            $stmt->execute(['maxDate' => $maxDate]);
        } else {
            echo json_encode([]); // Нет данных
            exit;
        }
    } elseif ($year && $month) {
        // Общий случай: фильтрация по году и месяцу (если не oiltransfer или reservoirvolumes)
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE YEAR(date) = :year AND MONTH(date) = :month");
        $stmt->execute(['year' => $year, 'month' => $month]);
    } else {
        // Без фильтра — вся таблица
        $stmt = $pdo->prepare("SELECT * FROM $tableName");
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

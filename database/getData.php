<?php
header('Content-Type: application/json');
require_once 'db.php';

$tableName = $_GET['table'] ?? '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;

// Только допустимые таблицы
$allowedTables = ['Pipelines', 'Points', 'oiltransfer', 'reservoirvolumes', 'sumoil']; // добавь свои
if (!in_array($tableName, $allowedTables)) {
    echo json_encode(['error' => 'Недопустимая таблица']);
    exit;
}

try {
    // Для таблиц с датой — нужна фильтрация
    if ($year && $month && in_array($tableName, ['oiltransfer', 'reservoirvolumes', 'sumoil'])) {
        // Найдём последнюю дату за указанный месяц
        $stmt = $pdo->prepare("
            SELECT MAX(date) AS latest_date
            FROM {$tableName}
            WHERE YEAR(date) = :year AND MONTH(date) = :month
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);
        $latestDate = $stmt->fetchColumn();

        if ($latestDate) {
            // Возвращаем записи только за эту дату
            $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE DATE(date) = :date");
            $stmt->execute(['date' => $latestDate]);
        } else {
            echo json_encode([]); // Нет данных
            exit;
        }
    }
    // Если год и месяц заданы, но таблица без особого режима (например, Points или Pipelines)
    elseif ($year && $month) {
        $stmt = $pdo->prepare("
            SELECT * FROM {$tableName}
            WHERE YEAR(date) = :year AND MONTH(date) = :month
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);
    }
    // Если вообще без фильтрации — отдать всё
    else {
        $stmt = $pdo->prepare("SELECT * FROM {$tableName}");
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

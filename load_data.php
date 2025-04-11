<?php
header("Content-Type: application/json");
$servername = "localhost";
$username = "user";
$password = "oil4815162342";
$dbname = "mapoil";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Ошибка подключения к БД"]));
}

$date = $_GET['date'] ?? null;
if (!$date) {
    die(json_encode(["success" => false, "message" => "Дата не указана"]));
}

// Запрос данных для трубопроводов
$oiltransferQuery = $conn->prepare("SELECT * FROM oiltransfer WHERE date = ?");
$oiltransferQuery->bind_param("s", $date);
$oiltransferQuery->execute();
$oiltransferResult = $oiltransferQuery->get_result();
$oiltransfers = $oiltransferResult->fetch_all(MYSQLI_ASSOC);
$oiltransferQuery->close();

// Запрос данных для резервуаров
$reservoirQuery = $conn->prepare("SELECT * FROM reservoirvolumes WHERE date = ?");
$reservoirQuery->bind_param("s", $date);
$reservoirQuery->execute();
$reservoirResult = $reservoirQuery->get_result();
$reservoirs = $reservoirResult->fetch_all(MYSQLI_ASSOC);
$reservoirQuery->close();

$last_reservoirs = [];

if (count($reservoirs) === 0) {
    // Если нет данных на выбранную дату — берём последнюю запись по каждому резервуару до этой даты
    $lastReservoirQuery = "
        SELECT rv1.*
        FROM reservoirvolumes rv1
        INNER JOIN (
            SELECT reservoir_id, MAX(date) AS max_date
            FROM reservoirvolumes
            WHERE date < ?
            GROUP BY reservoir_id
        ) rv2 ON rv1.reservoir_id = rv2.reservoir_id AND rv1.date = rv2.max_date
    ";

    $stmt = $conn->prepare($lastReservoirQuery);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $lastResult = $stmt->get_result();
    $last_reservoirs = $lastResult->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();

// Ответ
echo json_encode([
    "success" => true,
    "oiltransfers" => $oiltransfers,
    "reservoirs" => $reservoirs,
    "last_reservoirs" => $last_reservoirs
]);
?>

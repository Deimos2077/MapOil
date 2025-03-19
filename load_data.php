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

// Запрос данных для резервуаров
$reservoirQuery = $conn->prepare("SELECT * FROM reservoirvolumes WHERE date = ?");
$reservoirQuery->bind_param("s", $date);
$reservoirQuery->execute();
$reservoirResult = $reservoirQuery->get_result();
$reservoirs = $reservoirResult->fetch_all(MYSQLI_ASSOC);

$oiltransferQuery->close();
$reservoirQuery->close();
$conn->close();

echo json_encode(["success" => true, "oiltransfers" => $oiltransfers, "reservoirs" => $reservoirs]);
?>

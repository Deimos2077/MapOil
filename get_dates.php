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

// Запрос всех уникальных дат
$sql = "SELECT DISTINCT date FROM oiltransfer";
$result = $conn->query($sql);

$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row["date"];
}

$conn->close();
echo json_encode($dates);
?>

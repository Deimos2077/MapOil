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

// Получаем JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !is_array($data)) {
    die(json_encode(["success" => false, "message" => "Некорректные данные"]));
}

// Подготовленный SQL-запрос
$stmt = $conn->prepare("INSERT INTO oiltransfer (date, pipeline_id, piplines_system_id, from_point_id, to_point_id, from_amount, losses, to_amount, loss_coefficient) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($data as $row) {
    // Принудительно приводим loss_coefficient к float
    $loss_coefficient = floatval($row["loss_coefficient"]);

    $stmt->bind_param("siiiidddd", 
        $row["date"], 
        $row["pipeline_id"], 
        $row["piplines_system_id"], 
        $row["from_point_id"], 
        $row["to_point_id"], 
        $row["from_amount"], 
        $row["losses"], 
        $row["to_amount"], 
        $loss_coefficient // используем уже преобразованное значение
    );
    
    $stmt->execute();
}

// Закрываем соединение
$stmt->close();
$conn->close();
echo json_encode(["success" => true]);
?>

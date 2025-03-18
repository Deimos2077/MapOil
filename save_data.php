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

// Получаем дату из первого элемента массива (предполагается, что все записи имеют одинаковую дату)
$date = $data[0]["date"];

// Удаляем старые записи по этой дате
$deleteStmt = $conn->prepare("DELETE FROM oiltransfer WHERE date = ?");
$deleteStmt->bind_param("s", $date);
$deleteStmt->execute();
$deleteStmt->close();

// Подготовленный SQL-запрос для вставки
$stmt = $conn->prepare("INSERT INTO oiltransfer (date, pipeline_id, piplines_system_id, from_point_id, to_point_id, from_amount, losses, to_amount, loss_coefficient) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($data as $row) {
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
        $loss_coefficient
    );
    
    $stmt->execute();
}

// Закрываем соединение
$stmt->close();
$conn->close();
echo json_encode(["success" => true]);
?>

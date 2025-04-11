<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
if (!$data || !isset($data["oiltransfers"]) || !isset($data["reservoirs"])) {
    die(json_encode(["success" => false, "message" => "Некорректные данные"]));
}

// Если есть данные по трубопроводам
if (!empty($data["oiltransfers"])) {
    $date = $data["oiltransfers"][0]["date"];

    // Удаляем старые записи по этой дате
    $deleteStmt = $conn->prepare("DELETE FROM oiltransfer WHERE date = ?");
    $deleteStmt->bind_param("s", $date);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Подготовленный SQL-запрос для вставки
    $stmt = $conn->prepare("INSERT INTO oiltransfer (date, pipeline_id, piplines_system_id, from_point_id, to_point_id, from_amount, losses, to_amount, loss_coefficient) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($data["oiltransfers"] as $row) {
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

    $stmt->close();
}

// Если есть данные по резервуарам
if (!empty($data["reservoirs"])) {
    $date = $data["reservoirs"][0]["date"];

    // Удаляем старые записи по этой дате
    $deleteStmt = $conn->prepare("DELETE FROM reservoirvolumes WHERE date = ?");
    $deleteStmt->bind_param("s", $date);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Подготовленный SQL-запрос для вставки
    $stmt = $conn->prepare("
        INSERT INTO reservoirvolumes 
        (date, reservoir_id, start_volume, end_volume, minus_volume, plus_volume) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($data["reservoirs"] as $row) {
        // Устанавливаем значения, если они есть, иначе по умолчанию 0
        $minus_volume = isset($row["minus_volume"]) ? $row["minus_volume"] : 0;
        $plus_volume = isset($row["plus_volume"]) ? $row["plus_volume"] : 0;

        $stmt->bind_param("siidii", 
            $row["date"], 
            $row["reservoir_id"], 
            $row["start_volume"], 
            $row["end_volume"],
            $minus_volume,
            $plus_volume
        );

        $stmt->execute();
    }

    $stmt->close();
}

// Закрываем соединение
$conn->close();
echo json_encode(["success" => true]);
?>

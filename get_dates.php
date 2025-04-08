<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "user";
$password = "oil4815162342";
$dbname = "mapoil";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode([]));
}

// Получаем все уникальные даты
$sql = "SELECT DISTINCT date FROM oiltransfer ORDER BY date";
$result = $conn->query($sql);

$allDates = [];
$latestDates = []; // ключ = ГГГГ-ММ, значение = последняя дата

while ($row = $result->fetch_assoc()) {
    $date = $row["date"];
    $allDates[] = $date;

    // Извлекаем год-месяц
    $monthKey = substr($date, 0, 7); // "YYYY-MM"

    // Если текущая дата больше, чем сохранённая — обновляем
    if (!isset($latestDates[$monthKey]) || $date > $latestDates[$monthKey]) {
        $latestDates[$monthKey] = $date;
    }
}

$conn->close();

echo json_encode([
    "allDates" => $allDates,
    "latestPerMonth" => array_values($latestDates)
]);
?>

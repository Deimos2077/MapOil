<?php
require_once 'db.php'; // Подключение к БД

$query = "SELECT DISTINCT YEAR(date) as year, MONTH(date) as month FROM oiltransfer
          UNION
          SELECT DISTINCT YEAR(date), MONTH(date) FROM reservoirvolumes";
$result = $pdo->query($query);
$dates = $result->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($dates);
?>

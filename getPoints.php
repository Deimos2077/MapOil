<?php
// getPoints.php
header('Content-Type: application/json');
$host = 'localhost'; // замените на ваш хост
$db = 'mapoil'; // замените на имя вашей базы
$user = 'root'; // замените на имя пользователя
$password = ''; // замените на пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT id, name, lat, lng, color FROM points');
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($points);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

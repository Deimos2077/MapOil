<?php
if (!isset($_POST['date'])) {
    echo '0';
    exit;
}

$date = $_POST['date'];

// Подключение к БД
$host = 'localhost';
$db   = 'mapoil';
$user = 'user';
$pass = 'oil4815162342';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->prepare("
        SELECT 
            SUM(from_amount) AS total_from,
            SUM(to_amount) AS total_to
        FROM oiltransfer
        WHERE piplines_system_id BETWEEN 1 AND 6 AND date = ?
    ");
    $stmt->execute([$date]);

    $result = $stmt->fetch();
    $total = ($result['total_from'] ?? 0) + ($result['total_to'] ?? 0);

    echo number_format($total, 2, '.', ' ');

} catch (PDOException $e) {
    echo 'Ошибка БД';
}
?>
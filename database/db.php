<?php
// db.php
$host = 'localhost'; 
// $host = '192.168.1.23';
$dbname = 'mapoil';
// $user = 'user'; 
// $password = 'oil4815162342'; 
$user = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

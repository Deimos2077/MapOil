<?php
// db.php
$host = 'localhost'; // Хост, у нас все локально
$dbname = 'mapoil';
$user = 'user'; // Ваш пользователь базы данных
$password = 'oil4815162342'; // Ваш пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

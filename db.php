<?php
// db.php
$host = 'localhost';
$dbname = 'mapoil';
$user = 'root'; // Ваш пользователь базы данных
$password = ''; // Ваш пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

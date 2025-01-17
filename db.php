<?php
// Настройки подключения
$host = 'localhost';
$dbname = 'mapoil';
$user = 'root'; // Ваш пользователь базы данных
$password = ''; // Ваш пароль

try {
    // Создаем соединение
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // Устанавливаем режим ошибок PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

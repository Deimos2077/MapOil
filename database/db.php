<?php
// database/db.php
$host = 'localhost';
$port = '3306'; // Добавляем порт MySQL (обычно 3306)
$dbname = 'mapoil';
$user = 'root'; // Ваш пользователь базы данных
$password = ''; // Ваш пароль (оставьте пустым, если нет пароля)

try {
    // Подключение к базе данных
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Включаем выброс исключений
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // По умолчанию массив (без индексов)
        PDO::ATTR_EMULATE_PREPARES => false, // Отключаем эмуляцию подготовленных запросов (безопасность)
    ]);
} catch (PDOException $e) {
    error_log("❌ Ошибка подключения к базе данных: " . $e->getMessage(), 3, 'database_errors.log');
    die("❌ Ошибка подключения к базе данных. Проверьте настройки.");
}
?>

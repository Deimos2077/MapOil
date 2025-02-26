<?php
session_start();
require_once 'database/db.php';

// Проверка аутентификации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получение данных пользователя
$user_id = $_SESSION['user_id'];

// Получение истории входов для текущего пользователя
$stmt = $pdo->prepare("SELECT * FROM login_history WHERE user_id = :user_id ORDER BY login_time DESC");
$stmt->execute(['user_id' => $user_id]);
$login_history = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История входов</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>История входов</h2>
    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя пользователя</th>
                <th>IP-адрес</th>
                <th>Время входа</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($login_history as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['id']) ?></td>
                    <td><?= htmlspecialchars($entry['username']) ?></td>
                    <td><?= htmlspecialchars($entry['ip_address']) ?></td>
                    <td><?= htmlspecialchars($entry['login_time']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

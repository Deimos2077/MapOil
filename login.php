<?php
session_start();
require_once 'database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Отладочный вывод
    echo "Логин: " . $username . "<br>";
    echo "Пароль: " . $password . "<br>";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Пользователь найден: " . print_r($user, true) . "<br>";
    } else {
        echo "Пользователь не найден<br>";
    }

    // Временная проверка пароля
    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        header('Location: map.php');
        exit;
    } else {
        echo "Неверное имя пользователя или пароль";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<form method="POST">
    <h2>Login</h2>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
</body>
</html>


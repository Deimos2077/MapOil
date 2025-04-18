<?php
session_start();
require_once 'database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];



    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();



    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        header('Location: map.php');
        exit;
    } else {
        echo "<script type='text/javascript'>alert('Неверное имя пользователя или пароль');</script>";
    }

    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
    
        // Запись в таблицу login_history
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("INSERT INTO login_history (user_id, username, ip_address) VALUES (:user_id, :username, :ip_address)");
        $stmt->execute([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'ip_address' => $ip_address,
        ]);
    
        header('Location: map.php');
        exit;
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Авторизация</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<form method="POST">
    <h2>Авторизация</h2>
    <input type="text" name="username" placeholder="Логин" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <button type="submit">Войти</button>
</form>
</body>
</html>


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

// Обработка формы изменения пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка совпадения нового пароля и подтверждения
    if ($new_password !== $confirm_password) {
        $error = "Новый пароль и подтверждение не совпадают.";
    } else {
        // Проверка текущего пароля
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            $error = "Неверный текущий пароль.";
        } else {
            // Хэширование нового пароля
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Обновление пароля в базе данных
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute(['password' => $hashed_password, 'id' => $user_id]);

            $success = "Пароль успешно изменен.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Смена пароля</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Смена пароля</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="current_password">Текущий пароль:</label><br>
        <input type="password" id="current_password" name="current_password" required><br><br>

        <label for="new_password">Новый пароль:</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <label for="confirm_password">Подтвердите новый пароль:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <button type="submit">Изменить пароль</button>
    </form>
</body>
</html>

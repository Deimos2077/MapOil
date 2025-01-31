<?php
include('database/db.php'); // Подключаем файл для работы с БД

// SQL запрос для получения данных
$query = "SELECT oiltransfer.id, pipelines.company, points_from.name AS from_point, points_to.name AS to_point, oiltransfer.date, oiltransfer.from_amount, oiltransfer.to_amount, oiltransfer.losses 
          FROM oiltransfer
          JOIN pipelines ON oiltransfer.pipeline_id = pipelines.id
          JOIN points AS points_from ON oiltransfer.from_point_id = points_from.id
          JOIN points AS points_to ON oiltransfer.to_point_id = points_to.id";

$stmt = $pdo->query($query);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Данные о перекачке нефти</title>
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/menu.css">
</head>
<body>
<nav id="slide-menu">
    <ul>
        <li class="timeline"><a class="menu-href" href="http://localhost/oilgraf/">Графики</a></li>
        <li class="events"><a class="menu-href" href="http://localhost/mapoilds/MapOil/table.php">МатОтчет</a></li>
        <li class="calendar"><a class="menu-href" href="http://localhost/mapoilds/MapOil/map.php">Карта</a></li>
        <li class="sep settings">Settings</li>
        <li class="logout"><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<div id="content">
<div class="menu-trigger"></div>
    <h1>Данные о перекачке нефти</h1>
    <table id="oil-transfer-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Компания</th>
                <th>Точка отправления</th>
                <th>Точка назначения</th>
                <th>Дата</th>
                <th>Отправлено (тонн)</th>
                <th>Получено (тонн)</th>
                <th>Потери (тонн)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['company']; ?></td>
                    <td><?php echo $row['from_point']; ?></td>
                    <td><?php echo $row['to_point']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['from_amount']; ?></td>
                    <td><?php echo $row['to_amount']; ?></td>
                    <td><?php echo $row['losses']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
            </div>
    <script src= "js/menu.js"></script>
    <script src="js/table.js"></script> <!-- Подключаем JavaScript -->
</body>
</html>

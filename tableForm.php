<?php
require 'database/db.php'; // Подключаем базу данных

// Получаем имя таблицы из параметра URL
$table = isset($_GET['table']) ? $_GET['table'] : 'oiltransfer';

// Проверяем, что таблица существует
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];
if (!in_array($table, $allowedTables)) {
    die('Недопустимая таблица.');
}

// Получаем данные из выбранной таблицы
$query = $pdo->query("SELECT * FROM $table");
$rows = $query->fetchAll(PDO::FETCH_ASSOC);

// Получаем список колонок
$columns = array_keys($rows[0] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table: <?= htmlspecialchars($table) ?></title>
    <link rel="stylesheet" href="tableForm/style.css">
</head>
<body>
    <div class="container">
        <h1>Таблица: <?= htmlspecialchars($table) ?></h1>
        <table id="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <div class="table-switcher">
    <a href="/project/MapOil/map.php">Главная</a>
    <button onclick="switchTable('oiltransfer')">Oil Transfer</button>
    <button onclick="switchTable('pipelines')">Pipelines</button>
    <button onclick="switchTable('points')">Points</button>
    <button onclick="switchTable('reservoirs')">Reservoirs</button>
    <button onclick="switchTable('reservoirvolumes')">Reservoir Volumes</button>
</div>
                <?php foreach ($rows as $row): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <?php foreach ($columns as $col): ?>
                            <td contenteditable="true"><?= htmlspecialchars($row[$col]) ?></td>
                        <?php endforeach; ?>
                        <td>
                            <button class="save-btn">Сохранить</button>
                            <button class="delete-btn">Удалить</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button id="add-row-btn">Добавить запись</button>
    </div>
    <script src="tableForm/form.js"></script>
    <script>
        function switchTable(tableName) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('table', tableName);
    window.location.href = currentUrl.toString();
}

    </script>
</body>
</html>

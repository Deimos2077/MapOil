<?php
// Подключение к базе данных
include('database/db.php');

try {
    // Получаем все строки из базы данных
    $stmt = $pdo->query("SELECT * FROM oil_report_values ORDER BY row_number ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Ошибка при получении данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Данные о перекачке нефти</title>
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/modal_set.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/language.js"></script>
    <script src="js/Settings.js"></script>
    <script src= "js/menu.js"></script>

</head>
<body>

<!-- Модальное окно -->
<div id="settings-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Настройки</h2>
        <ul>
            <li>
                <span data-i18n="settings_language">Язык интерфейса:</span>
                <select id="language-select">
                    <option value="ru">Русский</option>
                    <option value="en">English</option>
                    <option value="zh">中文</option>
                </select>
            </li>
            <li>
                <span data-i18n="settings_font_size">Размер шрифта:</span>
                <input type="range" id="font-size" min="12" max="24" step="1">
            </li>
            <li><a href="#" id="export-excel" data-i18n="settings_export_excel">Экспорт данных в Excel</a></li>
            <li><a href="#" id="export-pdf" data-i18n="settings_export_pdf">Экспорт данных в PDF</a></li>
            <li>
                <span data-i18n="settings_email_report">Отчет по email:</span>
                <input type="email" id="email" placeholder="Введите email">
                <button id="send-report">Отправить</button>
            </li>
            <li><a href="#" id="help-button" data-i18n="settings_help">Помощь</a></li>
            <li><a href="/project/MapOil/password_change.php" data-i18n="settings_password_change">Смена пароля</a></li>
            <li><a href="/project/MapOil/login_history.php" data-i18n="settings_login_history">История входов</a></li>
        </ul>
    </div>
</div>    

<nav id="slide-menu">
    <ul>
        <li class="timeline"><a class="menu-href" href="http://localhost/oilgraf/" data-i18n="menu_graphs">Графики</a></li>
        <li class="events"><a class="menu-href" href="/project/MapOil/table.php" data-i18n="menu_reports">МатОтчет</a></li>
        <li class="timeline"><a class="menu-href" href="/project/MapOil/map.php" data-i18n="menu_map">Карта</a></li>
       
        <li class="svg-editor">
            <a class="menu-href" href="/project/svgedit-master/dist/editor/" target="_blank">Редактор SVG</a>
        </li>

        <!-- Настройки -->
        <li class="settings">
            <a href="#" id="settings-toggle" data-i18n="menu_settings">Настройки</a>
        </li>

        <li class="logout"><a href="logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>

    <div id="content">
        <div class="menu-trigger"></div>
        <h1>Данные о перекачке нефти</h1>

        <form class="ExcelForm" action="process.php" method="post" enctype="multipart/form-data">
            <input type="file" name="excelFile" accept=".xlsx, .xls" required>
            <button type="submit">Импортировать</button>
            <button type="submit">Сохранить данные</button>
        </form>

        <script src="js/menu.js"></script>
        <script src="js/table.js"></script>

        <table>
            <thead>
                <tr>
                    <th>№ п/п</th>
                    <th>Наименование</th>
                    <th>Номер и дата договора</th>
                    <th>Кол-во (тонн брутто)</th>
                    <th>Кол-во (тонн нетто)</th>
                    <th>Добыча за январь (тонн)</th>
                    <th>Добыча с начала года (тонн)</th>
                    <th>Сдача с начала года (тонн)</th>
                    <th>Технологические потери</th>
                    <th>Остатки на начало периода</th>
                    <th>Остатки на конец периода</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['row_number']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="contract_number"><?= htmlspecialchars($row['contract_number'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="from_amount"><?= htmlspecialchars($row['from_amount'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="to_amount"><?= htmlspecialchars($row['to_amount'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="january_production"><?= htmlspecialchars($row['january_production'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="yearly_production"><?= htmlspecialchars($row['yearly_production'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="yearly_delivery"><?= htmlspecialchars($row['yearly_delivery'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="losses"><?= htmlspecialchars($row['losses'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="start_volume"><?= htmlspecialchars($row['start_volume'] ?? '-') ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-column="end_volume"><?= htmlspecialchars($row['end_volume'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    
    <script>
        $(document).ready(function() {
            $("td[contenteditable=true]").blur(function() {
                var id = $(this).data("id");
                var column = $(this).data("column");
                var value = $(this).text();

                $.ajax({
                    url: "update.php",
                    method: "POST",
                    data: { id: id, column: column, value: value },
                    success: function(response) {
                        console.log("Обновлено: " + response);
                    },
                    error: function() {
                        alert("Ошибка обновления данных.");
                    }
                });
            });
        });
    </script>
</body>
</html>

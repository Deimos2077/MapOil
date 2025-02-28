<?php
session_start();
require_once 'database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Коэффициенты потерь (из Excel)
$loss_coefficients = [
    'psp_45' => 0.000332,
    'jana_zhol' => 0.000377,
    'pkop' => 0.1818,
    'atasu' => 0.0754,
    'border' => 0.1122
];
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Калькулятор перекачки нефти</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/modal_set.css">
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
        <li class="calculator"><a class="menu-href" href="/project/MapOil/calculator.php" data-i18n="menu_calculator">"Калькулятор"</a></li>
       
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

    <h1>Калькулятор перекачки нефти</h1>

    <!-- Ввод данных -->
    <h2>Ввод поступления нефти</h2>
    <table id="input-table">
        <thead>
            <tr>
                <th>Источник</th>
                <th>Объем нефти (тонн)</th>
                <th>Коэффициент потерь</th>
                <th>Потери (тонн)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ПСП 45</td>
                <td><input type="number" id="psp_45" class="input-field" value="0"></td>
                <td><?= $loss_coefficients['psp_45'] ?></td>
                <td id="loss_psp_45">0</td>
            </tr>
            <tr>
                <td>Жана Жол</td>
                <td><input type="number" id="jana_zhol" class="input-field" value="0"></td>
                <td><?= $loss_coefficients['jana_zhol'] ?></td>
                <td id="loss_jana_zhol">0</td>
            </tr>
        </tbody>
    </table>

    <!-- Распределение нефти -->
    <h2>Распределение нефти</h2>
    <table id="distribution-table">
        <thead>
            <tr>
                <th>Получатель</th>
                <th>Запланированный объем (тонн)</th>
                <th>Реально поступило (тонн)</th>
                <th>Потери (тонн)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ПКОП</td>
                <td><input type="number" id="pkop_plan" class="input-field" value="0"></td>
                <td id="pkop_real">0</td>
                <td id="pkop_loss">0</td>
            </tr>
            <tr>
                <td>Атасу</td>
                <td><input type="number" id="atasu_plan" class="input-field" value="0"></td>
                <td id="atasu_real">0</td>
                <td id="atasu_loss">0</td>
            </tr>
            <tr>
                <td>Граница 1235,3 км</td>
                <td><input type="number" id="border_plan" class="input-field" value="0"></td>
                <td id="border_real">0</td>
                <td id="border_loss">0</td>
            </tr>
        </tbody>
    </table>

    <h3>Итого:</h3>
    <p>Общий объем поступившей нефти: <span id="total-input">0</span> тонн</p>
    <p>Общий объем распределенной нефти: <span id="total-output">0</span> тонн</p>
    <p>Общие потери: <span id="total-loss">0</span> тонн</p>
    <p id="error-message" style="color: red; display: none;">Ошибка: поступившая нефть не равна распределенной!</p>

    <button id="save-data">Сохранить данные</button>
</div>

<!-- JS для расчетов -->
<script>
$(document).ready(function() {
    function updateTotals() {
        let psp_45 = parseFloat($("#psp_45").val()) || 0;
        let jana_zhol = parseFloat($("#jana_zhol").val()) || 0;
        let pkop_plan = parseFloat($("#pkop_plan").val()) || 0;
        let atasu_plan = parseFloat($("#atasu_plan").val()) || 0;
        let border_plan = parseFloat($("#border_plan").val()) || 0;

        let loss_psp_45 = psp_45 * <?= isset($loss_coefficients['psp_45']) ? $loss_coefficients['psp_45'] : 0 ?>;
        let loss_jana_zhol = jana_zhol * <?= isset($loss_coefficients['jana_zhol']) ? $loss_coefficients['jana_zhol'] : 0 ?>;

        let pkop_loss = pkop_plan * <?= isset($loss_coefficients['pkop']) ? $loss_coefficients['pkop'] : 0 ?>;
        let atasu_loss = atasu_plan * <?= isset($loss_coefficients['atasu']) ? $loss_coefficients['atasu'] : 0 ?>;
        let border_loss = border_plan * <?= isset($loss_coefficients['border']) ? $loss_coefficients['border'] : 0 ?>;

        let pkop_real = pkop_plan - pkop_loss;
        let atasu_real = atasu_plan - atasu_loss;
        let border_real = border_plan - border_loss;

        let totalInput = psp_45 + jana_zhol;
        let totalOutput = pkop_real + atasu_real + border_real + loss_psp_45 + loss_jana_zhol;

        $("#loss_psp_45").text(loss_psp_45.toFixed(2));
        $("#loss_jana_zhol").text(loss_jana_zhol.toFixed(2));
        $("#pkop_real").text(pkop_real.toFixed(2));
        $("#atasu_real").text(atasu_real.toFixed(2));
        $("#border_real").text(border_real.toFixed(2));
        $("#total-input").text(totalInput.toFixed(2));
        $("#total-output").text(totalOutput.toFixed(2));

        if (Math.abs(totalInput - totalOutput) > 0.01) {
            $("#error-message").show();
        } else {
            $("#error-message").hide();
        }
    }

    $(".input-field").on("input", updateTotals);
});

</script>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/language.js"></script>
    <script src="js/Settings.js"></script>
    <script src="js/menu.js"></script>
</body>
</html>
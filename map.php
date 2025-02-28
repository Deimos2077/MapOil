<?php
session_start();
require_once 'database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карта</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/leaflet.legend.css">
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

        <li class="logout"><a href=ы"logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>
<div id="content">
    <div class="menu-trigger"></div>
    <div class="head"></div>
    <h1 data-i18n="title">Карта нефтепроводов</h1>
    <div class="btnList">
        <ul class="ks-cboxtags">
            <li><input type="checkbox" id="checkboxOne" value="Oil" checked><label for="checkboxOne">Нефть</label></li>
            <li><input type="checkbox" id="checkboxTwo" value="Cotton Candy"><label for="checkboxTwo">Cotton Candy</label></li>
            <li><input type="checkbox" id="checkboxThree" value="Rarity"><label for="checkboxThree">Rarity</label></li>
            <li><input type="checkbox" id="checkboxFour" value="Moondancer"><label for="checkboxFour">Moondancer</label></li>
            <li><input type="checkbox" id="checkboxFive" value="Surprise"><label for="checkboxFive">Surprise</label></li>
            
     <div id="filter-container">
        <label for="dateFilter">Выберите месяц:</label>
            <input type="month" id="dateFilter">
            <button id="applyDateFilter">Применить</button>
     </div>

        </ul>     
    </div>
    <div id="map">
        <div id="legend" class="legend">
                    <div id="legend-header">
                        <strong>Легенда</strong>
                        <span id="legend-toggle">▼</span>
                    </div>
            <div id="legend-content">
                <ul>
                    <li><span class="pipeline" style="background-color: rgb(3, 198, 252);"></span> Нефтепровод АО КазТрансОйл</li>
                    <li><span class="pipeline" style="background-color: rgb(79, 73, 239);"></span> Нефтепровод ПАО Транснефть</li>
                    <li><span class="pipeline" style="background-color: rgb(5, 186, 53);"></span> Нефтепровод ТОО <br>«Казахстанско-Китайский трубопровод»</li>
                    <li><span class="pipeline" style="background-color: rgb(221, 5, 221);"></span> Нефтепровод АО 'СЗТК' МунайТас'</li>
                    <li><span class="circle" style="background-color: white; border: 2px solid black;"></span> Приемо-сдаточный пункт</li>
                    <li><span class="circle" style="background-color: red; border: 2px solid black;"></span> Приемо-сдаточный пункт с резервуарами для <br>хранения остатков товарной нефти</li><li><span class="cylinder-T" style="background-color: rgb(239, 17, 17)"></span> Резервуар для хранения <br>остатков товарной нефти</li>
                    <li><span class="cylinder-S" style="background-color: #88d279;"></span> Нефтепровод для хранения <br>остатков технологической нефти</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="info-table-container">
    <h3 data-i18n="info_table_title">Движение нефти по трубопроводам</h3>
    <p data-i18n="info_table_description">В этой таблице представлена информация о перемещении нефти между точками системы.</p>
    
    <table id="info-table">
        <thead>
            <tr>
                <th>Маршрут (Источник → Получатель)</th>
                <th>Объем нефти (тонн)</th>
                <th>Потери при транспортировке (тонн)</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button id="add-row-btn" data-i18n="button_add_record">Добавить новую запись</button>
</div>

<script src="js/legend.js"></script>
<script src="js/language.js"></script>
<script src="js/Settings.js"></script>
<script src="js/export.js"></script>
<script src= "js/menu.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.7.1/leaflet.polylineDecorator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator/dist/leaflet.polylineDecorator.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-easyprint"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.3.2/leaflet.polylineDecorator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="js/script.js"></script>
<script src="js/date_filt.js"></script>
</body>
</html>

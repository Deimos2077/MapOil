<?php
session_start();
require_once 'database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Функция для получения графиков за указанный месяц
function getChartsForMonth($month) {
    $start_date = date("Y-m-01", strtotime($month));
    $end_date = date("Y-m-t", strtotime($month));

    $pythonBin = "C:\Program Files\Python313\python.exe"; // Путь к Python
    if (!file_exists($pythonBin)) {
        $pythonBin = "C:\Python39\python.exe"; // Альтернативный путь к Python
    }
    $pythonScript = __DIR__ . DIRECTORY_SEPARATOR . "generate_charts.py";
    $command = "\"$pythonBin\" \"$pythonScript\" $start_date $end_date 2>error.log";


    $output = shell_exec($command);
    file_put_contents("debug.log", "CMD: $command\nOUTPUT:\n$output");

    $charts = json_decode($output, true);
    return $charts;
}

// Обработка AJAX-запроса для выбранного месяца
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['month'])) {
    $month = $_POST['month'];
    $charts = getChartsForMonth($month);
    echo json_encode($charts);
    exit;
}

// Загрузка данных за текущий месяц при открытии страницы
$current_month = date("Y-m"); // Например, "2025-04" (сегодня апрель 2025 по условиям системы)
$charts = getChartsForMonth($current_month);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">


    <style>

.highlight-date {
    background: #ffcc00 !important; /* Цвет фона */
    color: black !important;
    border-radius: 50%;
}
#date-input {
    width: 100%;
    max-width: 300px;
    padding: 8px;
    font-size: 16px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    background-color: #fff;
    transition: border-color 0.3s, box-shadow 0.3s;
}

#date-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    outline: none;
}

label[for="date-input"] {
    font-weight: bold;
    margin-bottom: 5px;
    display: inline-block;
}

    </style>
</head>
<body>

<div id="global-preloader" class="preloader-overlay">
  <div class="loader"></div>
</div>

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
        <li class="map"><a class="menu-href" href="/project/MapOil/map.php" ><i class="fa fa-map" ></i>Карта транспортировки нефти</a></li>        
        <li class="calculator"><a class="menu-href" href="/project/MapOil/calculator.php" data-i18n="menu_calculator"><i class="fa fa-file-text"></i>Отчетность</a></li>
        <li class="timeline"><a class="menu-href" href="/project/Graph/analysis.php" data-i18n="menu_graphs">Графики</a></li>
        <!-- <li class="events"><a class="menu-href" href="/project/MapOil/table.php" data-i18n="menu_reports">МатОтчет</a></li> -->



        
        <!-- <li class="svg-editor">
            <a class="menu-href" href="/project/svgedit-master/dist/editor/" target="_blank">Редактор SVG</a>
        </li> -->

        <!-- Настройки -->
        <!-- <li class="settings">
            <a href="#" id="settings-toggle" data-i18n="menu_settings">Настройки</a>
        </li> -->

        <li class="logout"><a href="logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>
<div id="content">
    <div class="menu-trigger"></div>
    <div class="head"></div>
    <h1 data-i18n="title">Карта нефтепроводов</h1>
    <div class="btnList">
        <ul class="ks-cboxtags">
            <li><input type="checkbox" id="checkboxOne" value="Oil" checked><label for="checkboxOne">Нефть</label></li>
            <li><input type="checkbox" id="checkboxTwo" value="Cotton Candy"><label for="checkboxTwo">Резервуары</label></li>
            <!-- <li><input type="checkbox" id="checkboxThree" value="Rarity"><label for="checkboxThree">Rarity</label></li>
            <li><input type="checkbox" id="checkboxFour" value="Moondancer"><label for="checkboxFour">Moondancer</label></li>
            <li><input type="checkbox" id="checkboxFive" value="Surprise"><label for="checkboxFive">Surprise</label></li> -->
            

<div id="filter-container">
  <label for="month-input">Месяц:</label>
  <input type="text" id="month-input" placeholder="Выберите месяц" class="form-control">
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

    <div id="charts">
    <h2><i class="fas fa-chart-line"></i> Графики</h2>
    <div id="preloader">Загрузка...</div>
    <div id="chart-container">
        <?php
        if ($charts && !isset($charts['error']) &&
            isset($charts['final_price']) && $charts['final_price'] !== "Нет данных" &&
            isset($charts['total_amount']) && $charts['total_amount'] !== "Нет данных" &&
            isset($charts['net_tons']) && $charts['net_tons'] !== "Нет данных") {           
             echo "<div class=\"chart\">
                    <h3><i class=\"fas fa-weight-hanging\"></i> Нетто (тонны)</h3>
                    <iframe src='" . htmlspecialchars($charts['net_tons']) . "' style='width:100%; max-width:580px; height:400px;'></iframe>
                    <p class=\"caption\">Масса нефти нетто в тоннах</p>
                  </div>";
            echo "<div class=\"chart\">
                    <h3><i class=\"fas fa-dollar-sign\"></i> Итоговая цена</h3>
                    <iframe src='" . htmlspecialchars($charts['final_price']) . "' style='width:100%; max-width:580px; height:400px;'></iframe>
                    <p class=\"caption\">Итоговая стоимость нефти за период</p>
                  </div>";
            echo "<div class=\"chart\">
                    <h3><i class=\"fas fa-oil-can\"></i> Общий объём</h3>
                    <iframe src='" . htmlspecialchars($charts['total_amount']) . "' style='width:100%; max-width:580px; height:400px;'></iframe>
                    <p class=\"caption\">Общий объём нефти за период</p>
                  </div>";

        } else {
            echo "<p>Нет данных за этот месяц.</p>";
        }
        ?>
    </div>
</div>



    <div id="blur-background"></div>

    <div id="pointModal" class="modal">
      <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Заголовок</h2>
        <div id="modalBody">Загрузка...</div>
      </div>
    </div>






    <!-- <div id="info-table-container">
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
</div> -->
<!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch("get_dates.php") // Загружаем даты с сервера
            .then(response => response.json())
            .then(dates => {
                flatpickr("#date-input", {
                    dateFormat: "Y-m-d",
                    locale: "ru",
                    onDayCreate: function(dObj, dStr, fp, dayElem) {
                        let dateStr = dayElem.dateObj.toLocaleDateString("sv-SE"); // YYYY-MM-DD без часового пояса
                        if (dates.includes(dateStr)) {
                            dayElem.classList.add("highlight-date"); // Подсвечиваем дату
                        }
                    }
                });
            })
            .catch(error => console.error("Ошибка загрузки дат:", error));
    });
    </script> -->

    <script>
        document.getElementById('month-input').addEventListener('change', function() {
            var month = this.value;
            if (month) {
                document.getElementById('preloader').style.display = 'block';
                document.getElementById('chart-container').innerHTML = '';
                fetch('map.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'month=' + month
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('preloader').style.display = 'none';
                    var container = document.getElementById('chart-container');
                    if (data && !data.error &&
                        data.donut_direction !== "Нет данных" &&
                        data.final_price !== "Нет данных" &&
                        data.total_amount !== "Нет данных" &&
                        data.net_tons !== "Нет данных") {
                        container.innerHTML = `
                            <div class="chart"><p><iframe src="${data.net_tons}" width="580" height="400"></iframe></p></div>
                            <div class="chart"><p><iframe src="${data.final_price}" width="580" height="400"></iframe></p></div>
                            <div class="chart"><p><iframe src="${data.total_amount}" width="580" height="400"></iframe></p></div>
                        `;
                    } else {
                        container.innerHTML = '<p>Нет данных за этот месяц.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('preloader').style.display = 'none';
                    console.error('Ошибка:', error);
                });
            }
        });
    </script>

    <script>
flatpickr("#month-input", {
    locale: "ru",
    allowInput: true, 
    clickOpens: true, 
    wrap: false,      
    plugins: [
        new monthSelectPlugin({
            shorthand: false,
            dateFormat: "Y-m", 
            altFormat: "F Y",
            theme: "light"
        })
    ]
});

</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>


<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

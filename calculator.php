<?php 
session_start();
require_once 'database/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохранение данных о потерях нефти</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/modal_set.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-input {
            background-color: #ffcccc !important;
            border: 2px solid red;
            font-weight: bold;
        }
        #date-input{
            width: 17%;
        }
        .gray-input{
            background-color:rgb(218, 217, 217) !important;
            font-weight: bold;
        }
        .highlight-date {
        background: #ffcc00 !important; /* Жёлтая подсветка */
        color: black !important;
        border-radius: 50%;
    }
    .highlight-last-day {
        background: #ff4d4d !important; /* Красная подсветка */
        color: white !important;
        border-radius: 50%;
    }
    </style>
        
<script>
async function saveData() {
    const date = document.getElementById("date-input").value;
    if (!date) {
        alert("Пожалуйста, выберите дату.");
        return;
    }

    // Получаем значения для sumoil
    const oilplane = parseInt(document.getElementById("oilplane").value || 0);
    const oil = parseInt(document.getElementById("oil").value || 0);
    const oilLoss = parseInt(document.getElementById("oil-loss").value || 0);

    const sumoil = [{
        date,
        oilplane,
        oil,
        oilLoss
    }];

    let oiltransfers = [];
    let reservoirs = [];

    const pipelineTables = document.querySelectorAll("table[data-type='pipelines']");
    const reservoirTables = document.querySelectorAll("table[data-type='reservoirs']");

    console.log("Найдено таблиц трубопроводов:", pipelineTables.length);
    console.log("Найдено таблиц резервуаров:", reservoirTables.length);

    pipelineTables.forEach(table => {
        const pipelinesSystemId = table.getAttribute("data-pipelines-system-id");
        table.querySelectorAll("tbody tr").forEach(row => {
            const pipelineId = row.getAttribute("data-pipeline-id");
            const fromPointId = row.getAttribute("data-from-id");
            const toPointId = row.getAttribute("data-to-id");

            if (!pipelineId || !fromPointId || !toPointId) {
                console.warn("Пропущена строка, отсутствуют data-* атрибуты:", row);
                return;
            }

            const getNumber = (selector) => {
                let input = row.querySelector(selector);
                return input && input.value.trim() !== "" ? parseFloat(input.value.replace(',', '.')) || 0 : 0;
            };

            oiltransfers.push({
                date,
                pipeline_id: parseInt(pipelineId),
                piplines_system_id: parseInt(pipelinesSystemId),
                from_point_id: parseInt(fromPointId),
                to_point_id: parseInt(toPointId),
                loss_coefficient: getNumber("[id^='percent-']"),
                from_amount: getNumber("[id^='volume-']"),
                losses: getNumber("[id^='loss-']"),
                to_amount: getNumber("[id^='volume2-']")
            });
        });
    });

    reservoirTables.forEach(table => {
        table.querySelectorAll("tbody tr").forEach(row => {
            const reservoirId = row.getAttribute("reservoir_id");
            if (!reservoirId) {
                console.warn("Пропущена строка, отсутствует reservoir_id:", row);
                return;
            }

            reservoirs.push({
                date,
                reservoir_id: parseInt(reservoirId),
                start_volume: parseInt(row.querySelector("[id^='start-']")?.value || "0"),
                end_volume: parseInt(row.querySelector("[id^='end-']")?.value || "0"),
                minus_volume: parseInt(row.querySelector("[id^='minus-']")?.value || "0"),
                plus_volume: parseInt(row.querySelector("[id^='plus-']")?.value || "0")
            });
        });
    });

    console.log("Отправляемые oiltransfers:", oiltransfers);
    console.log("Отправляемые reservoirs:", reservoirs);
    console.log("Отправляемые данные для sumoil:", sumoil);

    const data = {
        oiltransfers,
        reservoirs,
        sumoil
    };

    try {
        const response = await fetch('save_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const text = await response.text();
        console.log("Ответ сервера:", text);

        const result = JSON.parse(text);
        if (result.success) {
            alert("Данные успешно сохранены!");
        } else {
            alert("Ошибка: " + result.message);
        }
    } catch (error) {
        alert("Ошибка при отправке данных: " + error);
    }
}



// загрузка данных

async function loadData() {
    const dateInput = document.getElementById("date-input");
    const date = dateInput.value;

    if (!date) {
        alert("Пожалуйста, выберите дату.");
        return;
    }

    try {
        const response = await fetch(`load_data.php?date=${date}`);
        const result = await response.json();

        if (!result.success) {
            alert("Ошибка: " + result.message);
            return;
        }

        console.log("Загруженные данные:", result);

        // Заполняем данные для трубопроводов
        result.oiltransfers.forEach(row => {
            let allRows = document.querySelectorAll(`tr[data-pipeline-id="${row.pipeline_id}"]`);

            allRows.forEach(tr => {
                // Проверяем, не находится ли tr внутри #myTable
                if (!tr.closest("#myTable")) {
                    const percent = tr.querySelector("[id^='percent-']");
                    const volume = tr.querySelector("[id^='volume-']");
                    const loss = tr.querySelector("[id^='loss-']");
                    const volume2 = tr.querySelector("[id^='volume2-']");

                    if (percent) percent.value = row.loss_coefficient ?? "";
                    if (volume) volume.value = row.from_amount ?? "";
                    if (loss) loss.value = row.losses ?? "";
                    if (volume2) volume2.value = row.to_amount ?? "";
                }
            });
        });

        // Заполнение резервуаров — если есть записи на выбранную дату
        if (result.reservoirs && result.reservoirs.length > 0) {
            result.reservoirs.forEach(row => {
                document.querySelectorAll(`tr[reservoir_id="${row.reservoir_id}"]`).forEach(tr => {
                    if (!tr.closest("#myTable")) {
                        const start = tr.querySelector("[id^='start-']");
                        const end = tr.querySelector("[id^='end-']");
                        const minus = tr.querySelector("[id^='minus-']");
                        const plus = tr.querySelector("[id^='plus-']");

                        if (start) start.value = row.start_volume ?? "";
                        if (end) end.value = row.end_volume ?? "";
                        if (minus) minus.value = row.minus_volume ?? "";
                        if (plus) plus.value = row.plus_volume ?? "";
                    }
                });
            });
        }

        // Если на выбранную дату нет резервуаров, берём последнюю запись и подставляем end_volume → start_volume
        else if (result.last_reservoirs && result.last_reservoirs.length > 0) {
            result.last_reservoirs.forEach(row => {
                document.querySelectorAll(`tr[reservoir_id="${row.reservoir_id}"]`).forEach(tr => {
                    if (!tr.closest("#myTable")) {
                        const start = tr.querySelector("[id^='start-']");
                        if (start) start.value = row.end_volume ?? "";
                    }
                });
            });
        }

        // Заполнение данных для sumoil
        if (result.sumoil) {
            const oilplaneInput = document.getElementById("oilplane");
            const oilInput = document.getElementById("oil");
            const oilLossInput = document.getElementById("oil-loss");

            if (oilplaneInput) oilplaneInput.value = result.sumoil.oilplane ?? "";
            if (oilInput) oilInput.value = result.sumoil.oil ?? "";
            if (oilLossInput) oilLossInput.value = result.sumoil.oil_loss ?? "";
        }

    } catch (error) {
        alert("Ошибка при загрузке данных: " + error);
    }
}


</script>
</head>
<body class="container mt-4">
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
        <li class="report"><a class="menu-href" href="/project/MapOil/ExcelMathReport.php" ><i class="fa fa-file"></i>МатОтчет</a></li>
        <!-- <li class="svg-editor">
            <a class="menu-href" href="/project/svgedit-master/dist/editor/" target="_blank">Редактор SVG</a>
        </li> -->
        <!-- <li class="settings"><a href="#" id="settings-toggle" data-i18n="menu_settings">Настройки</a></li> -->
        <li class="logout"><a href="logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>
<div id="content">
<div class="menu-trigger"></div>

    <h2 class="mb-4">Форма расчета потерь нефти</h2>
    <label style="display:block" for="date-input">Дата:</label>
    <input type="date" id="date-input" class="form-control mb-3" onchange="loadData();loadDataEx();">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary mb-4" onclick="exportToExcel()" >Экспортировать Excel</button>
    </div>
    <table class="table table-bordered col-1" data-type="oilplane">
        <thead>
            <tr class="table-primary">
                <th>План сдачи нефти</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td><input type="number" id="oilplane" class="form-control"></td>
        </tr>
        </tbody>
    </table>

    <table class="table table-bordered" data-type="oilfact">
        <thead>
            <tr class="table-primary">
                <th>Факт сдачи нефти</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td><input type="number" id="oil" class="form-control"></td>
            <td><input type="number" id="oil-loss" class="form-control"></td>
        </tr>
        </tbody>
    </table>
    <h3 class="mb-4" style="display:block">Остатки</h3>
    <table class="table table-bordered" data-type="reservoirs">
        <thead>
            <tr class="table-primary">
                <th>Резеруары</th>
                <th>Остатки на начало месяца</th>
                <th>Остатки на конец месяца</th>
                <th>Вытеснено </th>
                <th>Пополнено</th>
            </tr>
        </thead>
        <tbody>
        <tr reservoir_id="1">
            <td>ПСП45 (АО "КАЗТРАНСОЙЛ")</td>
            <td><input type="number" id="start-volumePsp" class="form-control"></td>
            <td><input type="number" id="end-volumePsp" class="form-control"></td>
            <td><input type="number" id="minus-volumePsp" class="form-control"></td>
            <td><input type="number" id="plus-volumePsp" class="form-control"></td>
        </tr>
        <tr reservoir_id="5">
            <td>МН ТОО «Казахстанско-Китайский трубопровод»</td>
            <td><input type="number" id="start-volume1" class="form-control"></td>
            <td><input type="number" id="end-volume1" class="form-control"></td>
            <td><input type="number" id="minus-volume1" class="form-control"></td>
            <td><input type="number" id="plus-volume1" class="form-control"></td>
        </tr>
        <tr reservoir_id="3">
            <td>ГНПС Кумколь (АО "КАЗТРАНСОЙЛ")</td>
            <td><input type="number" id="start-volumeKumkol" class="form-control"></td>
            <td><input type="number" id="end-volumeKumkol" class="form-control"></td>
            <td><input type="number" id="minus-volumeKumkol" class="form-control"></td>
            <td><input type="number" id="plus-volumeKumkol" class="form-control"></td>
        </tr>
        <tr reservoir_id="6">
            <td>МН АО "СЗТК "Мунайтас"</td>
            <td><input type="number" id="start-volume2" class="form-control"></td>
            <td><input type="number" id="end-volume2" class="form-control"></td>
            <td><input type="number" id="minus-volume2" class="form-control"></td>
            <td><input type="number" id="plus-volume2" class="form-control"></td>
        </tr>
        <tr reservoir_id="2">
            <td>НПС им.Шманова (АО "КАЗТРАНСОЙЛ")</td>
            <td><input type="number" id="start-volumeShmanova" class="form-control"></td>
            <td><input type="number" id="end-volumeShmanova" class="form-control"></td>
            <td><input type="number" id="minus-volumeShmanova" class="form-control"></td>
            <td><input type="number" id="plus-volumeShmanova" class="form-control"></td>
        </tr>
        <tr reservoir_id="4">
            <td>ПСП Самара</td>
            <td><input type="number" id="start-volume" class="form-control"></td>
            <td><input type="number" id="end-volume" class="form-control"></td>
            <td><input type="number" id="minus-volume" class="form-control"></td>
            <td><input type="number" id="plus-volume" class="form-control"></td>
        </tr>
        </tbody>
    </table>
    <h3 class="mb-4">Внутренний рынок (ПКОП)</h3>
    <table class="table table-bordered" data-pipelines-system-id="1" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>Процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>Принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="24" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end" class="form-control"></td>
            <td><input type="number" id="loss-pspP" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="25" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol" class="form-control"></td>
            <td><input type="number" id="loss-zhanazholP" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit" class="form-control gray-input"></td>
        </tr>
            <tr data-pipeline-id="26" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="27" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyakPP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakP" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="28" data-from-id="4" data-to-id="6">
                <td>ГНПС Кумколь</td>
                <td>ПСП ПКОП</td>
                <td><input type="number" id="percent-pkopPP" class="form-control" step="0.0001" value="0.1818"></td>
                <td><input type="number" id="volume-kumkol" class="form-control"></td>
                <td><input type="number" id="loss-pkopP" class="form-control"></td>
                <td><input type="number" id="volume2-pkop" class="form-control main-input"></td>
            </tr>
        </tbody>
    </table>
    

<h3 class="mb-4">КНР</h3>
    <table class="table table-bordered" data-pipelines-system-id="2" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>Процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>Принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="29" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end2" class="form-control"></td>
            <td><input type="number" id="loss-psp2P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first2" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="30" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol2" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol2P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit2" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="31" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer2" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer2P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak2" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="32" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyakPP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak2" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak2P" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol2" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="33" data-from-id="4" data-to-id="14">
                <td>ГНПС Кумколь</td>
                <td>ГНПС им. Б. Джумагалиева</td>
                <td><input type="number" id="percent-kumkolPP" class="form-control" step="0.0001" value="0.0525"></td>
                <td><input type="number" id="volume-kumkol2" class="form-control"></td>
                <td><input type="number" id="loss-kumkol2P" class="form-control"></td>
                <td><input type="number" id="volume2-dzhumagalieva" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="34" data-from-id="14" data-to-id="2">
                <td>ГНПС им. Б. Джумагалиева</td>
                <td>ГНПС Атасу</td>
                <td><input type="number" id="percent-dzhumagalievaPP" class="form-control" step="0.0001" value="0.0754"></td>
                <td><input type="number" id="volume-dzhumagalieva" class="form-control"></td>
                <td><input type="number" id="loss-dzhumagalievaP" class="form-control"></td>
                <td><input type="number" id="volume2-atasuTransfer" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="35" data-from-id="26" data-to-id="26">
                <td colspan="2">ГНПС Атасу (перевалка в н/п Атасу -Алашанькоу)</td>
                <td><input type="number" id="percent-atasuTransferPP" class="form-control" step="0.0001" value="0.0051"></td>
                <td><input type="number" id="volume-atasuTransfer" class="form-control"></td>
                <td><input type="number" id="loss-atasuTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-atasu" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="36" data-from-id="2" data-to-id="1">
                <td>ГНПС Атасу</td>
                <td>Алашанькоу</td>
                <td><input type="number" id="percent-alashankouPP" class="form-control" step="0.0001" value="0.0965"></td>
                <td><input type="number" id="volume-atasu" class="form-control main-input"></td>
                <td><input type="number" id="loss-alashankouP" class="form-control"></td>
                <td><input type="number" id="volume2-alashankou" class="form-control"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Европа (Усть-Луга)</h3>
    <table class="table table-bordered" data-pipelines-system-id="3" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>Процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>Принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="37" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end3" class="form-control"></td>
            <td><input type="number" id="loss-psp3P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first3" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="38" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol3" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol3P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit3" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="39" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer3" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer3P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak3" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="40" data-from-id="5" data-to-id="7">
                <td>ГНПС Кенкияк</td>
                <td>НПС им. Шманова</td>
                <td><input type="number" id="percent-shmanovaPP" class="form-control" step="0.0001" value="0.0429"></td>
                <td><input type="number" id="volume-kenkiyak3" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak3P" class="form-control"></td>
                <td><input type="number" id="volume2-shmanova" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="41" data-from-id="7" data-to-id="19">
                <td>НПС им. Шманова</td>
                <td>НПС им. Касымова</td>
                <td><input type="number" id="percent-kasimovaPP" class="form-control" step="0.0001" value="0.0455"></td>
                <td><input type="number" id="volume-shmanova" class="form-control"></td>
                <td><input type="number" id="loss-shmanovaP" class="form-control"></td>
                <td><input type="number" id="volume2-kasimova" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="42" data-from-id="19" data-to-id="24">
                <td>НПС им. Касымова</td>
                <td>1235,3 км граница (РК/РФ)</td>
                <td><input type="number" id="percent-km1235PP" class="form-control" step="0.0001" value="0.1122"></td>
                <td><input type="number" id="volume-kasimova" class="form-control"></td>
                <td><input type="number" id="loss-kasimovaP" class="form-control"></td>
                <td><input type="number" id="volume2-km1235" class="form-control main-input"></td>
            </tr>
            <tr data-pipeline-id="43" data-from-id="24" data-to-id="8">
                <td>1235,3 км граница (РК/РФ)</td>
                <td>Самара</td>
                <td><input type="number" id="percent-samaraPP" class="form-control" step="0.0001" value="0.0192"></td>
                <td><input type="number" id="volume-km1235" class="form-control"></td>
                <td><input type="number" id="loss-samaraP" class="form-control"></td>
                <td><input type="number" id="volume2-samara" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="44" data-from-id="27" data-to-id="27">
                <td colspan="2">СамараСамара БСН (на Дружбу) (перевалка)</td>
                <td><input type="number" id="percent-samaraTransferPP" class="form-control" step="0.0001" value="0.0137"></td>
                <td><input type="number" id="volume-samara" class="form-control"></td>
                <td><input type="number" id="loss-samaraTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-samaraTransfer" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="45" data-from-id="8" data-to-id="16">
                <td>Самара</td>
                <td>Клин</td>
                <td><input type="number" id="percent-klinPP" class="form-control" step="0.0001" value="0.0000"></td>
                <td><input type="number" id="volume-samaraTransfer" class="form-control"></td>
                <td><input type="number" id="loss-klinP" class="form-control"></td>
                <td><input type="number" id="volume2-klin" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="46" data-from-id="16" data-to-id="20">
                <td>Клин</td>
                <td>Никольское</td>
                <td><input type="number" id="percent-nikolskiPP" class="form-control" step="0.0001" value="0.0065"></td>
                <td><input type="number" id="volume-klin" class="form-control"></td>
                <td><input type="number" id="loss-nikolskiP" class="form-control"></td>
                <td><input type="number" id="volume2-nikolski" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="47" data-from-id="20" data-to-id="23">
                <td>Никольское</td>
                <td>Унеча (на Андреаполь)</td>
                <td><input type="number" id="percent-unechaPP" class="form-control" step="0.0001" value="0.0458"></td>
                <td><input type="number" id="volume-nikolski" class="form-control"></td>
                <td><input type="number" id="loss-unechaP" class="form-control"></td>
                <td><input type="number" id="volume2-unecha" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="48" data-from-id="23" data-to-id="10">
                <td colspan="2">НБ Усть-Луга (перевалка)</td>
                <td><input type="number" id="percent-ustlugaTransferPP" class="form-control" step="0.0001" value="0.0258"></td>
                <td><input type="number" id="volume-unecha" class="form-control"></td>
                <td><input type="number" id="loss-ustlugaTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-ustlugaTransfer" class="form-control"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Ответ.хранение</h3>
    <table class="table table-bordered" data-pipelines-system-id="4" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>Процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>Принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="49" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45end4" class="form-control"></td>
            <td><input type="number" id="loss-psp4P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first4" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="50" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol4" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol4P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit4" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="51" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer4" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer4P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak4" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="52" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyak4PP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak4" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak4P" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol4" class="form-control main-input"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">ПНХЗ</h3>
    <table class="table table-bordered" data-pipelines-system-id="5" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>Процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>Принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="53" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end5" class="form-control"></td>
            <td><input type="number" id="loss-psp5P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first5" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="54" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol5" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol5P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit5" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="55" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer5" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer5P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="56" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyakPP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak5" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak5P" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="57" data-from-id="4" data-to-id="14">
                <td>ГНПС Кумколь</td>
                <td>ГНПС им. Б. Джумагалиева</td>
                <td><input type="number" id="percent-kumkolPP" class="form-control" step="0.0001" value="0.0525"></td>
                <td><input type="number" id="volume-kumkol5" class="form-control"></td>
                <td><input type="number" id="loss-kumkol5P" class="form-control"></td>
                <td><input type="number" id="volume2-dzhumagalieva5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="58" data-from-id="14" data-to-id="2">
                <td>ГНПС им. Б. Джумагалиева</td>
                <td>ГНПС Атасу</td>
                <td><input type="number" id="percent-dzhumagalievaPP" class="form-control" step="0.0001" value="0.0754"></td>
                <td><input type="number" id="volume-dzhumagalieva5" class="form-control"></td>
                <td><input type="number" id="loss-dzhumagalieva5P" class="form-control"></td>
                <td><input type="number" id="volume2-atasuTransfer5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="59" data-from-id="26" data-to-id="26">
                <td colspan="2">ГНПС Атасу (перевалка в н/п Атасу -Алашанькоу)</td>
                <td><input type="number" id="percent-atasuTransferPP" class="form-control" step="0.0001" value="0.0051"></td>
                <td><input type="number" id="volume-atasuTransfer5" class="form-control"></td>
                <td><input type="number" id="loss-atasuTransfer5P" class="form-control"></td>
                <td><input type="number" id="volume2-atasu5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="60" data-from-id="2" data-to-id="3">
                <td>ГНПС Атасу</td>
                <td>ПНХЗ</td>
                <td><input type="number" id="percent-pnhzPP" class="form-control" step="0.0001" value="0.0965"></td>
                <td><input type="number" id="volume-atasu5" class="form-control main-input"></td>
                <td><input type="number" id="loss-pnhzP" class="form-control"></td>
                <td><input type="number" id="volume2-pnhz" class="form-control"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Европа (Новороссийск)</h3>
    <table class="table table-bordered" data-pipelines-system-id="6" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>Процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>Принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="61" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end6" class="form-control"></td>
            <td><input type="number" id="loss-psp6P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first6" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="62" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol6" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol6P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit6" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="63" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer6" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer6P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak6" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="64" data-from-id="5" data-to-id="7">
                <td>ГНПС Кенкияк</td>
                <td>НПС им. Шманова</td>
                <td><input type="number" id="percent-shmanovaPP" class="form-control" step="0.0001" value="0.0429"></td>
                <td><input type="number" id="volume-kenkiyak6" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak6P" class="form-control"></td>
                <td><input type="number" id="volume2-shmanova6" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="65" data-from-id="7" data-to-id="19">
                <td>НПС им. Шманова</td>
                <td>НПС им. Касымова</td>
                <td><input type="number" id="percent-kasimovaPP" class="form-control" step="0.0001" value="0.0455"></td>
                <td><input type="number" id="volume-shmanova6" class="form-control"></td>
                <td><input type="number" id="loss-shmanova6P" class="form-control"></td>
                <td><input type="number" id="volume2-kasimova6" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="66" data-from-id="19" data-to-id="24">
                <td>НПС им. Касымова</td>
                <td>1235,3 км граница (РК/РФ)</td>
                <td><input type="number" id="percent-km1235PP" class="form-control" step="0.0001" value="0.1122"></td>
                <td><input type="number" id="volume-kasimova6" class="form-control"></td>
                <td><input type="number" id="loss-kasimova6P" class="form-control"></td>
                <td><input type="number" id="volume2-km12356" class="form-control main-input"></td>
            </tr>
            <tr data-pipeline-id="67" data-from-id="24" data-to-id="8">
                <td>1235,3 км граница (РК/РФ)</td>
                <td>Самара</td>
                <td><input type="number" id="percent-samaraPP" class="form-control" step="0.0001" value="0.0192"></td>
                <td><input type="number" id="volume-km12356" class="form-control"></td>
                <td><input type="number" id="loss-samara6P" class="form-control"></td>
                <td><input type="number" id="volume2-samara6" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="68" data-from-id="27" data-to-id="27">
                <td colspan="2">СамараСамара БСН (на Дружбу) (перевалка)</td>
                <td><input type="number" id="percent-samaraTransferPP" class="form-control" step="0.0001" value="0.0137"></td>
                <td><input type="number" id="volume-samara6" class="form-control"></td>
                <td><input type="number" id="loss-samaraTransfer6P" class="form-control"></td>
                <td><input type="number" id="volume2-samaraTransfer6" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="69" data-from-id="8" data-to-id="18">
                <td>Самара</td>
                <td>Красноармейск</td>
                <td><input type="number" id="percent-krasnoarmPP" class="form-control" step="0.0001" value="0.0098"></td>
                <td><input type="number" id="volume-samaraTransfer6" class="form-control"></td>
                <td><input type="number" id="loss-krasnoarmP" class="form-control"></td>
                <td><input type="number" id="volume2-krasnoarm" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="70" data-from-id="18" data-to-id="17">
                <td>Красноармейск</td>
                <td>915 км н/пр.КЛ </td>
                <td><input type="number" id="percent-km915PP" class="form-control" step="0.0001" value="0.0132"></td>
                <td><input type="number" id="volume-krasnoarm" class="form-control"></td>
                <td><input type="number" id="loss-km915P" class="form-control"></td>
                <td><input type="number" id="volume2-km915" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="71" data-from-id="17" data-to-id="21">
                <td>915 км н/пр.КЛ</td>
                <td>Родионовская</td>
                <td><input type="number" id="percent-radionovskiPP" class="form-control" step="0.0001" value="0.0000"></td>
                <td><input type="number" id="volume-km915" class="form-control"></td>
                <td><input type="number" id="loss-radionovskiP" class="form-control"></td>
                <td><input type="number" id="volume2-radionovski" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="72" data-from-id="21" data-to-id="22">
                <td>Родионовская</td>
                <td>Тихорецк</td>
                <td><input type="number" id="percent-texareckPP" class="form-control" step="0.0001" value="0.0108"></td>
                <td><input type="number" id="volume-radionovski" class="form-control"></td>
                <td><input type="number" id="loss-texareckP" class="form-control"></td>
                <td><input type="number" id="volume2-texareck" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="73" data-from-id="22" data-to-id="15">
                <td>Тихорецк</td>
                <td>Грушовая</td>
                <td><input type="number" id="percent-grushavaiPP" class="form-control" step="0.0001" value="0.0151"></td>
                <td><input type="number" id="volume-texareck" class="form-control"></td>
                <td><input type="number" id="loss-grushavaiP" class="form-control"></td>
                <td><input type="number" id="volume2-grushavai" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="74" data-from-id="27" data-to-id="27">
                <td colspan="2">ПК Шесхарис промплощадка Грушовая (перевалка)</td>
                <td><input type="number" id="percent-grushavaiTransferPP" class="form-control" step="0.0001" value="0.0295"></td>
                <td><input type="number" id="volume-grushavai" class="form-control"></td>
                <td><input type="number" id="loss-grushavaiTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-grushavaiTransfer" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="75" data-from-id="15" data-to-id="9">
                <td>1235,3 км граница (РК/РФ)</td>
                <td>Новороссийск</td>
                <td><input type="number" id="percent-grushavai999" class="form-control" step="0.0001" value="0.0151"></td>
                <td><input type="number" id="volume-grushavaiTransfer" class="form-control"></td>
                <td><input type="number" id="loss-grushavaiTransfer-5" class="form-control"></td>
                <td><input type="number" id="volume2-grushavaiTransfer-5" class="form-control"></td>
            </tr>
        </tbody>
    </table>
    <button class="btn btn-success" onclick="saveData(1)">Сохранить</button>
    <table id="myTable" style="display:none" >
    <tr>
        <td colspan="23" style="font-weight: bold; text-align: center;">Расчет потерь при транспортировке нефти</td>
    </tr>
    <tr>
        <td colspan="23"></td> <!-- Пустая строка -->
    </tr>
        <tr class="header-row">
            <th colspan="2">Нефтепровод</th>
            <th colspan="1">Нормативные технич. потерь</th>
            <th colspan="2">Остаток</th>
            <th colspan="3">Внутренний рынок (ПКОП)</th>
            <th colspan="3">КНР</th>
            <th colspan="3">Европа (Усть-Луга)</th>
            <th colspan="3">Отгст.хранение</th>
            <th colspan="3">Внутренний рынок (ПНХ3)</th>
            <th colspan="3">Европа (Новороссийск)</th>
        </tr>
        <tr class="header-row">
            <th>от</th>
            <th>до</th>
            <th></th>
            <th>на начало месяца</th>
            <th>на конец месяца</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
        </tr>
        <tr reservoir_id="1">
            <td class="left-align" colspan="2">По системе МН АО "КАЗТРАНСОЙЛ" (Западный Филиал)</td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td><span id="minus-volume"></span></td>
            <td><span id="plus-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="24,29,37,49,53,61"> 
            <td>ПСП 45 км</td>
            <td >ГНПС Кинкияк</td>
            <td><span  id="loss_coefficient">0,0332%</span></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td><span  id="from_amount3"></span></td>
            <td><span  id="losses3"></span></td>
            <td><span  id="to_amount3"></span></td> 
            <td><span  id="from_amount4"></span></td>
            <td><span  id="losses4"></span></td>
            <td><span  id="to_amount4"></span></td> 
            <td><span  id="from_amount5"></span></td>
            <td><span  id="losses5"></span></td>
            <td><span  id="to_amount5"></span></td> 
            <td><span  id="from_amount6"></span></td>
            <td><span  id="losses6"></span></td>
            <td><span  id="to_amount6"></span></td> 
        </tr>
        <tr data-pipeline-id="25,30,38,50,54,62">
            <td >КПОУ Жанажол</td>
            <td >ГНПС Кинкияк</td>
            <td><span id="percent-zhanazholPP">0,0377%</span></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td><span  id="from_amount3"></span></td>
            <td><span  id="losses3"></span></td>
            <td><span  id="to_amount3"></span></td> 
            <td><span  id="from_amount4"></span></td>
            <td><span  id="losses4"></span></td>
            <td><span  id="to_amount4"></span></td> 
            <td><span  id="from_amount5"></span></td>
            <td><span  id="losses5"></span></td>
            <td><span  id="to_amount5"></span></td> 
            <td><span  id="from_amount6"></span></td>
            <td><span  id="losses6"></span></td>
            <td><span  id="to_amount6"></span></td> 
        </tr>
        <tr data-pipeline-id="26,31,39,51,55,63">
            <td>ГНПС Кенкияк (перевалка)</td>
            <td></td>
            <td><span id="percent-kenkiyakTransferPP">0,0077%</span></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td><span  id="from_amount3"></span></td>
            <td><span  id="losses3"></span></td>
            <td><span  id="to_amount3"></span></td> 
            <td><span  id="from_amount4"></span></td>
            <td><span  id="losses4"></span></td>
            <td><span  id="to_amount4"></span></td> 
            <td><span  id="from_amount5"></span></td>
            <td><span  id="losses5"></span></td>
            <td><span  id="to_amount5"></span></td> 
            <td><span  id="from_amount6"></span></td>
            <td><span  id="losses6"></span></td>
            <td><span  id="to_amount6"></span></td> 
        </tr>
        <tr reservoir_id="5">
            <td class="left-align" colspan="2">По системе МН ТОО «Казахстанско-Китайский трубопровод»</td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td><span id="minus-volume"></span></td>
            <td><span id="plus-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="27,32,52,56,64">
            <td class="left-align">ГНПС Кенкияк</td>
            <td class="left-align">ГНПС Кумколь</td>
            <td>0,0794%</td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td></td>
            <td></td>
            <td></td> 
            <td><span  id="from_amount3"></span></td>
            <td><span  id="losses3"></span></td>
            <td><span  id="to_amount3"></span></td> 
            <td><span  id="from_amount4"></span></td>
            <td><span  id="losses4"></span></td>
            <td><span  id="to_amount4"></span></td>
            <td></td>
            <td></td>
            <td></td> 

        </tr>
        <tr reservoir_id="3">
            <td class="left-align" colspan="2">По системе МН АО "КАЗТРАНСОЙЛ" (Восточный Филиал)</td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td><span id="minus-volume"></span></td>
            <td><span id="plus-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="28">
            <td class="left-align">ГНПС Кумколь</td>
            <td class="left-align">ПСП ПКОП (прием по УУН)</td>
            <td>0,1818%</td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="33,57">
            <td class="left-align">ГНПС Кумколь</td>
            <td class="left-align">ГНПС им. Б. Джумагалиева</td>
            <td>0,0525%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="34,58">
            <td class="left-align">ГНПС им. Б. Джумагалиева</td>
            <td class="left-align">ГНПС Атасу</td>
            <td>0,0754%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="35,59">
            <td>ГНПС Атасу (перевалка в н/п Атасу -Алашанькоу)</td>
            <td></td>
            <td>0,0051%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td> 
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="60">
            <td class="left-align">ГНПС Атасу</td>
            <td class="left-align">ПНХЗ</td>
            <td>0,0843%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr >
            <td class="left-align" colspan="2">По системе МН ТОО «Казахстанско-Китайский трубопровод»</td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td><span id="minus-volume"></span></td>
            <td><span id="plus-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="36">
            <td class="left-align">ГНПС Атасу</td>
            <td class="left-align">Алашанькоу</td>
            <td>0,0965%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr reservoir_id="6">
            <td class="left-align" colspan="2">По системе МН АО "СЗТК "Мунайтас"</td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td><span id="minus-volume"></span></td>
            <td><span id="plus-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="40,64">
            <td class="left-align">ГНПС Кенкияк</td>
            <td class="left-align">НПС им. Шманова</td>
            <td>0,0429%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td>
        </tr>
        <tr reservoir_id="2">
            <td class="left-align" colspan="2">По системе МН АО "КАЗТРАНСОЙЛ" (Западный Филиал) </td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="41,65">
            <td class="left-align">НПС им. Шманова</td>
            <td class="left-align">НПС им. Касымова</td>
            <td>0,0455%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td>
        </tr>
        <tr data-pipeline-id="42,66">
            <td class="left-align">НПС им. Касымова</td>
            <td class="left-align">1235,3 км (граница (РК/РФ))</td>
            <td>0,1122%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td>
        </tr>
        <tr reservoir_id="4">
            <td class="left-align" colspan="2">По системе ПАО "Транснефть"</td>
            <td></td>
            <td><span id="start-volume"></span></td>
            <td><span id="end-volume"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="43,67">
            <td class="left-align">Граница РК/РФ (Б.Чернигов)</td>
            <td class="left-align">Самара</td>
            <td>0,0192%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td>
        </tr>
        <tr data-pipeline-id="44,68">
            <td>Самара БСН (на Дружбу) (перевалка)</td>
            <td></td>
            <td>0,0137%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount2"></span></td>
            <td><span  id="losses2"></span></td>
            <td><span  id="to_amount2"></span></td>
        </tr>
        <tr data-pipeline-id="45">
            <td class="left-align">Самара</td>
            <td class="left-align">Клин</td>
            <td>0%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="46">
            <td class="left-align">Клин</td>
            <td class="left-align">Никольское</td>
            <td>0,0065%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="47">
            <td class="left-align">Никольское</td>
            <td class="left-align">Унеча (на Андреаполь)</td>
            <td>0,0458%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="48">
            <td >НБ Усть-Луга (перевалка)</td>
            <td></td>
            <td>0,0258%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Погрузка нефти в танкер в порту Усть-Луга (перевалка)</td>
            <td></td>
            <td>-</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr data-pipeline-id="69">
            <td class="left-align">Самара</td>
            <td class="left-align">Красноармейск</td>
            <td>0,0098%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="70">
            <td class="left-align">Красноармейск</td>
            <td class="left-align">915 км н/пр.КЛ </td>
            <td>0,0132%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="71">
            <td class="left-align">915 км н/пр. КЛ</td>
            <td class="left-align">Родионовская</td>
            <td>0%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="72">
            <td class="left-align">Родионовская</td>
            <td class="left-align">Тихорецк</td>
            <td>0,0108%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="73">
            <td >ПНБ Тихорецкая (перевалка) </td>
            <td></td>
            <td>-</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="74">
            <td class="left-align">Тихорецк</td>
            <td class="left-align">Грушовая</td>
            <td>0,0151%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="75">
            <td>ПК Шесхарис промплощадка Грушовая (перевалка)</td>
            <td></td>
            <td>0,0295%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="76">
            <td>ПК Шесхарис промплощадка Шесхарис (перевалка для налива в танкеры)</td>
            <td></td>
            <td>-</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
        <tr data-pipeline-id="77">
            <td>Порт Новороссийск (перевалка)</td>
            <td></td>
            <td>-</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><span  id="from_amount1"></span></td>
            <td><span  id="losses1"></span></td>
            <td><span  id="to_amount1"></span></td>
        </tr>
    </table>
<div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch("get_dates.php")
            .then(response => response.json())
            .then(result => {
                const allDates = result.allDates;
                const latestPerMonth = result.latestPerMonth;

                flatpickr("#date-input", {
                    dateFormat: "Y-m-d",
                    locale: "ru",
                    onDayCreate: function(dObj, dStr, fp, dayElem) {
                        const dateStr = dayElem.dateObj.toLocaleDateString("sv-SE");

                        if (allDates.includes(dateStr)) {
                            if (latestPerMonth.includes(dateStr)) {
                                dayElem.classList.add("highlight-last-day"); // красный
                            } 
                            else {
                                dayElem.classList.add("highlight-date"); // жёлтый
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Ошибка загрузки дат:", error));
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/language.js"></script>
    <script src="js/Settings.js"></script>
    <script src="js/menu.js"></script>
    <script src="js/calculate.js"></script>
    <script src="js/excel.js"></script>
    
</body>
</html>

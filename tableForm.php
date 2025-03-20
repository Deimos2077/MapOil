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
    <style>
        .main-input {
            background-color: #ffcccc !important;
            border: 2px solid red;
            font-weight: bold;
        }
        #date-input{
            width: 17%;
        }
        .highlight-date {
            background: #ffcc00 !important; /* Цвет фона */
            color: black !important;
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
                start_volume: parseInt(row.querySelector("[id^='start-volume']")?.value || "0"),
                end_volume: parseInt(row.querySelector("[id^='end-volume']")?.value || "0")
            });
        });
    });

    console.log("Отправляемые oiltransfers:", oiltransfers);
    console.log("Отправляемые reservoirs:", reservoirs);

    const data = { oiltransfers, reservoirs };

    const response = await fetch('save_data.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
});

const text = await response.text();
console.log("Ответ сервера:", text);

try {
    const result = JSON.parse(text);
    if (result.success) {
        alert("Данные успешно сохранены!");
    } else {
        alert("Ошибка: " + result.message);
    }
} catch (error) {
    alert("Ошибка обработки JSON: " + error);
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
            let rows = document.querySelectorAll(`tr[data-pipeline-id="${row.pipeline_id}"]`);
            rows.forEach(tr => {
                tr.querySelector("[id^='percent-']").value = row.loss_coefficient ?? "";
                tr.querySelector("[id^='volume-']").value = row.from_amount ?? "";
                tr.querySelector("[id^='loss-']").value = row.losses ?? "";
                tr.querySelector("[id^='volume2-']").value = row.to_amount ?? "";
            });
        });

        // Заполняем данные для резервуаров
        result.reservoirs.forEach(row => {
            let rows = document.querySelectorAll(`tr[reservoir_id="${row.reservoir_id}"]`);
            rows.forEach(tr => {
                tr.querySelector("[id^='start-volume']").value = row.start_volume ?? "";
                tr.querySelector("[id^='end-volume']").value = row.end_volume ?? "";
            });
        });

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
        <li class="timeline"><a class="menu-href" href="http://localhost/oilgraf/" data-i18n="menu_graphs">Графики</a></li>
        <li class="events"><a class="menu-href" href="/project/MapOil/table.php" data-i18n="menu_reports">МатОтчет</a></li>
        <li class="timeline"><a class="menu-href" href="/project/MapOil/map.php" data-i18n="menu_map">Карта</a></li>
        <li class="calculator"><a class="menu-href" href="/project/MapOil/calculator.php" data-i18n="menu_calculator">Реализация нефти</a></li>
        <li class="svg-editor"><a class="menu-href" href="/project/svgedit-master/dist/editor/" target="_blank">Редактор SVG</a></li>
        <li class="settings"><a href="#" id="settings-toggle" data-i18n="menu_settings">Настройки</a></li>
        <li class="logout"><a href="logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>
<div id="content">
<div class="menu-trigger"></div>

    <h2 class="mb-4">Форма расчета потерь нефти</h2>
    <label style="display:block" for="date-input">Дата:</label>
    <input type="date" id="date-input" class="form-control mb-3" onchange="loadData()">


    <h3 class="mb-4">Остатки</h3>
    <table class="table table-bordered" data-type="reservoirs">
        <thead>
            <tr class="table-primary">
                <th>Резеруары</th>
                <th>Начало месяца</th>
                <th>Конец месяца</th>
                <th>Взято</th>
                <th>Отдано</th>
            </tr>
        </thead>
        <tbody>
        <tr reservoir_id="1">
            <td>ПСП 45 км</td>
            <td><input type="number" id="start-volume" class="form-control"></td>
            <td><input type="number" id="end-volume" class="form-control"></td>
            <td><input type="number" id="minus-volume" class="form-control"></td>
            <td><input type="number" id="plus-volume" class="form-control"></td>
        </tr>
        <tr reservoir_id="2">
            <td>НПС им. Шманова</td>
            <td><input type="number" id="start-volume" class="form-control"></td>
            <td><input type="number" id="end-volume" class="form-control"></td>
            <td><input type="number" id="minus-volume" class="form-control"></td>
            <td><input type="number" id="plus-volume" class="form-control"></td>
        </tr>
        <tr reservoir_id="3">
            <td>ГНПС Кумколь</td>
            <td><input type="number" id="start-volume" class="form-control"></td>
            <td><input type="number" id="end-volume" class="form-control"></td>
            <td><input type="number" id="minus-volume" class="form-control"></td>
            <td><input type="number" id="plus-volume" class="form-control"></td>
        </tr>
        <!-- <tr reservoir_id="4">
            <td>ПСП Самара</td>
            <td><input type="number" id="start-volume" class="form-control"></td>
            <td><input type="number" id="end-volume" class="form-control"></td>
            <td><input type="number" id="minus-volume" class="form-control"></td>
            <td><input type="number" id="plus-volume" class="form-control"></td>
        </tr> -->
        <tr reservoir_id="5">
            <td>Технический резервуар 1</td>
            <td><input type="number" id="start-volume" class="form-control"></td>
            <td><input type="number" id="end-volume" class="form-control"></td>
            <td><input type="number" id="minus-volume" class="form-control"></td>
            <td><input type="number" id="plus-volume" class="form-control"></td>
        </tr>
        <tr reservoir_id="6">
            <td>Технический резервуар 2</td>
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
                <th>процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="18" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end" class="form-control"></td>
            <td><input type="number" id="loss-pspP" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol" class="form-control"></td>
            <td><input type="number" id="loss-zhanazholP" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="1" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyakPP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakP" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="14" data-from-id="4" data-to-id="6">
                <td>ГНПС Кумколь</td>
                <td>ПСП ПКОП</td>
                <td><input type="number" id="percent-pkopPP" class="form-control" step="0.0001" value="0.1818"></td>
                <td><input type="number" id="volume-kumkol" class="form-control"></td>
                <td><input type="number" id="loss-pkopP" class="form-control"></td>
                <td><input type="number" id="volume2-pkop" class="form-control main-input"></td>
            </tr>
        </tbody>
    </table>
    <!-- <button class="btn btn-success" onclick="calculate(1)">Расчитать</button> -->

<!-- <h3 class="mb-4">КНР</h3>
    <table class="table table-bordered" data-pipelines-system-id="2" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="18" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45-2" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45-2" class="form-control"></td>
            <td><input type="number" id="loss-psp45-2" class="form-control"></td>
            <td><input type="number" id="volume2-psp45-2" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazhol-2" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol-2" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol-2" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazhol-2" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="1" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyak-transfer-2" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-2" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-2" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-2" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyak-2" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak-2" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-2" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-2" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="15" data-from-id="4" data-to-id="14">
                <td>ГНПС Кумколь</td>
                <td>ГНПС им. Б. Джумагалиева</td>
                <td><input type="number" id="percent-kumkol-2" class="form-control" step="0.0001" value="0.0525"></td>
                <td><input type="number" id="volume-kumkol-2" class="form-control"></td>
                <td><input type="number" id="loss-kumkol-2" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol-2 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="13" data-from-id="14" data-to-id="2">
                <td>ГНПС им. Б. Джумагалиева</td>
                <td>ГНПС Атасу</td>
                <td><input type="number" id="percent-dzhumagalieva-2" class="form-control" step="0.0001" value="0.0754"></td>
                <td><input type="number" id="volume-dzhumagalieva-2" class="form-control"></td>
                <td><input type="number" id="loss-dzhumagalieva-2" class="form-control"></td>
                <td><input type="number" id="volume2-dzhumagalieva-2 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="26" data-to-id="26">
                <td colspan="2">ГНПС Атасу (перевалка в н/п Атасу -Алашанькоу)</td>
                <td><input type="number" id="percent-atasuTransfer-2" class="form-control" step="0.0001" value="0.0051"></td>
                <td><input type="number" id="volume-atasuTransfer-2" class="form-control"></td>
                <td><input type="number" id="loss-atasuTransfer-2" class="form-control"></td>
                <td><input type="number" id="volume2-atasuTransfer-2 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="11" data-from-id="2" data-to-id="1">
                <td>ГНПС Атасу</td>
                <td>Алашанькоу</td>
                <td><input type="number" id="percent-alashankou-2" class="form-control" step="0.0001" value="0.0965"></td>
                <td><input type="number" id="volume-alashankou-2" class="form-control main-input"></td>
                <td><input type="number" id="loss-alashankou-2" class="form-control"></td>
                <td><input type="number" id="volume2-alashankou-2 " class="form-control"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Европа (Усть-Луга)</h3>
    <table class="table table-bordered" data-pipelines-system-id="3" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="18" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45-3" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45-3" class="form-control"></td>
            <td><input type="number" id="loss-psp45-3" class="form-control"></td>
            <td><input type="number" id="volume2-psp45-3" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazhol-3" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol-3" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol-3" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazhol-3" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="1" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyak-transfer-3" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-3" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-3" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-3" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="7">
                <td>ГНПС Кенкияк</td>
                <td>НПС им. Шманова</td>
                <td><input type="number" id="percent-kenkiyak-3" class="form-control" step="0.0001" value="0.0429"></td>
                <td><input type="number" id="volume-kenkiyak-3" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-3" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-3" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="7" data-to-id="19">
                <td>НПС им. Шманова</td>
                <td>НПС им. Касымова</td>
                <td><input type="number" id="percent-kumkol-3" class="form-control" step="0.0001" value="0.0455"></td>
                <td><input type="number" id="volume-kumkol-3" class="form-control"></td>
                <td><input type="number" id="loss-kumkol-3" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol-3 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="20" data-from-id="19" data-to-id="24">
                <td>НПС им. Касымова</td>
                <td>1235,3 км граница (РК/РФ)</td>
                <td><input type="number" id="percent-dzhumagalieva-3" class="form-control" step="0.0001" value="0.1122"></td>
                <td><input type="number" id="volume-dzhumagalieva-3" class="form-control"></td>
                <td><input type="number" id="loss-dzhumagalieva-3" class="form-control"></td>
                <td><input type="number" id="volume2-dzhumagalieva-3 " class="form-control main-input"></td>
            </tr>
            <tr data-pipeline-id="23" data-from-id="24" data-to-id="8">
                <td>1235,3 км граница (РК/РФ)</td>
                <td>Самара</td>
                <td><input type="number" id="percent-samara-3" class="form-control" step="0.0001" value="0.0192"></td>
                <td><input type="number" id="volume-samara-3" class="form-control"></td>
                <td><input type="number" id="loss-samara-3" class="form-control"></td>
                <td><input type="number" id="volume2-samara-3" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="27" data-to-id="27">
                <td colspan="2">СамараСамара БСН (на Дружбу) (перевалка)</td>
                <td><input type="number" id="percent-samaraTransfer-3" class="form-control" step="0.0001" value="0.0137"></td>
                <td><input type="number" id="volume-samaraTransfer-3" class="form-control"></td>
                <td><input type="number" id="loss-samaraTransfer-3" class="form-control"></td>
                <td><input type="number" id="volume2-samaraTransfer-3 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="2" data-from-id="8" data-to-id="16">
                <td>Самара</td>
                <td>Клин</td>
                <td><input type="number" id="percent-klin-3" class="form-control" step="0.0001" value="0.0000"></td>
                <td><input type="number" id="volume-klin-3" class="form-control"></td>
                <td><input type="number" id="loss-klin-3" class="form-control"></td>
                <td><input type="number" id="volume2-klin-3" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="3" data-from-id="16" data-to-id="20">
                <td>Клин</td>
                <td>Никольское</td>
                <td><input type="number" id="percent-nikolski-3" class="form-control" step="0.0001" value="0.0065"></td>
                <td><input type="number" id="volume-nikolski-3" class="form-control"></td>
                <td><input type="number" id="loss-nikolski-3" class="form-control"></td>
                <td><input type="number" id="volume2-nikolski-3 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="4" data-from-id="20" data-to-id="23">
                <td>Никольское</td>
                <td>Унеча (на Андреаполь)</td>
                <td><input type="number" id="percent-unecha-3" class="form-control" step="0.0001" value="0.0458"></td>
                <td><input type="number" id="volume-unecha-3" class="form-control"></td>
                <td><input type="number" id="loss-unecha-3" class="form-control"></td>
                <td><input type="number" id="volume2-unecha-3 " class="form-control"></td>
            </tr>
            <tr data-pipeline-id="22" data-from-id="23" data-to-id="10">
                <td colspan="2">НБ Усть-Луга (перевалка)</td>
                <td><input type="number" id="percent-ustlugaTransfer-3" class="form-control" step="0.0001" value="0.0258"></td>
                <td><input type="number" id="volume-ustlugaTransfer-3" class="form-control"></td>
                <td><input type="number" id="loss-ustlugaTransfer-3" class="form-control"></td>
                <td><input type="number" id="volume2-ustlugaTransfer-3" class="form-control"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Ответ.хранение</h3>
    <table class="table table-bordered" data-pipelines-system-id="4" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="18" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45-4" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45-4" class="form-control"></td>
            <td><input type="number" id="loss-psp45-4" class="form-control"></td>
            <td><input type="number" id="volume2-psp45-4" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazhol-4" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol-4" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol-4" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazhol-4" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="1" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyak-transfer-4" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-4" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-4" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-4" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyak-4" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak-4" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-4" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-4" class="form-control main-input"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Европа (Новороссийск)</h3>
    <table class="table table-bordered" data-pipelines-system-id="5" data-type="pipelines">
        <thead>
            <tr class="table-primary">
                <th>ОТ</th>
                <th>ДО</th>
                <th>процент потерь%</th>
                <th>Передано</th>
                <th>Потери (тонн)</th>
                <th>принято</th>
            </tr>
        </thead>
        <tbody>
        <tr data-pipeline-id="18" data-from-id="12" data-to-id="5">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45-5" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45-5" class="form-control"></td>
            <td><input type="number" id="loss-psp45-5" class="form-control"></td>
            <td><input type="number" id="volume2-psp45-5" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazhol-5" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol-5" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol-5" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazhol-5" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="17" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyak-transfer-5" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-5" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-5" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="7">
                <td>ГНПС Кенкияк</td>
                <td>НПС им. Шманова</td>
                <td><input type="number" id="percent-kenkiyak-5" class="form-control" step="0.0001" value="0.0429"></td>
                <td><input type="number" id="volume-kenkiyak-5" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-5" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="7" data-to-id="19">
                <td>НПС им. Шманова</td>
                <td>НПС им. Касымова</td>
                <td><input type="number" id="percent-kumkol-5" class="form-control" step="0.0001" value="0.0455"></td>
                <td><input type="number" id="volume-kumkol-5" class="form-control"></td>
                <td><input type="number" id="loss-kumkol-5" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="20" data-from-id="19" data-to-id="24">
                <td>НПС им. Касымова</td>
                <td>1235,3 км граница (РК/РФ)</td>
                <td><input type="number" id="percent-dzhumagalieva-5" class="form-control" step="0.0001" value="0.1122"></td>
                <td><input type="number" id="volume-dzhumagalieva-5" class="form-control"></td>
                <td><input type="number" id="loss-dzhumagalieva-5" class="form-control"></td>
                <td><input type="number" id="volume2-dzhumagalieva-5" class="form-control main-input"></td>
            </tr>
            <tr data-pipeline-id="21" data-from-id="24" data-to-id="8">
                <td>1235,3 км граница (РК/РФ)</td>
                <td>Самара</td>
                <td><input type="number" id="percent-samara-5" class="form-control" step="0.0001" value="0.0192"></td>
                <td><input type="number" id="volume-samara-5" class="form-control"></td>
                <td><input type="number" id="loss-samara-5" class="form-control"></td>
                <td><input type="number" id="volume2-samara-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="27" data-to-id="27">
                <td colspan="2">Самара БСН (на Дружбу) (перевалка)</td>
                <td><input type="number" id="percent-samaraTransfer-5" class="form-control" step="0.0001" value="0.0137"></td>
                <td><input type="number" id="volume-samaraTransfer-5" class="form-control"></td>
                <td><input type="number" id="loss-samaraTransfer-5" class="form-control"></td>
                <td><input type="number" id="volume2-samaraTransfer-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="5" data-from-id="8" data-to-id="18">
                <td>Самара</td>
                <td>Красноармейск</td>
                <td><input type="number" id="percent-krasnoarm-5" class="form-control" step="0.0001" value="0.0098"></td>
                <td><input type="number" id="volume-krasnoarm-5" class="form-control"></td>
                <td><input type="number" id="loss-krasnoarm-5" class="form-control"></td>
                <td><input type="number" id="volume2-krasnoarm-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="6" data-from-id="18" data-to-id="17">
                <td>Красноармейск</td>
                <td>915 км н/пр.КЛ </td>
                <td><input type="number" id="percent-km915-5" class="form-control" step="0.0001" value="0.0132"></td>
                <td><input type="number" id="volume-km915-5" class="form-control"></td>
                <td><input type="number" id="loss-km915-5" class="form-control"></td>
                <td><input type="number" id="volume2-km915-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="7" data-from-id="17" data-to-id="21">
                <td>915 км н/пр.КЛ</td>
                <td>Родионовская</td>
                <td><input type="number" id="percent-radionovski-5" class="form-control" step="0.0001" value="0.0000"></td>
                <td><input type="number" id="volume-radionovski-5" class="form-control"></td>
                <td><input type="number" id="loss-radionovski-5" class="form-control"></td>
                <td><input type="number" id="volume2-radionovski-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="8" data-from-id="21" data-to-id="22">
                <td>Родионовская</td>
                <td>Тихорецк</td>
                <td><input type="number" id="percent-texareck-5" class="form-control" step="0.0001" value="0.0108"></td>
                <td><input type="number" id="volume-texareck-5" class="form-control"></td>
                <td><input type="number" id="loss-texareck-5" class="form-control"></td>
                <td><input type="number" id="volume2-texareck-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="9" data-from-id="22" data-to-id="15">
                <td>Тихорецк</td>
                <td>Грушовая</td>
                <td><input type="number" id="percent-grushavai-5" class="form-control" step="0.0001" value="0.0151"></td>
                <td><input type="number" id="volume-grushavai-5" class="form-control"></td>
                <td><input type="number" id="loss-grushavai-5" class="form-control"></td>
                <td><input type="number" id="volume2-grushavai-5" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="10" data-from-id="15" data-to-id="9">
                <td colspan="2">ПК Шесхарис промплощадка Грушовая (перевалка)</td>
                <td><input type="number" id="percent-grushavaiTransfer-5" class="form-control" step="0.0001" value="0.0295"></td>
                <td><input type="number" id="volume-grushavaiTransfer-5" class="form-control"></td>
                <td><input type="number" id="loss-grushavaiTransfer-5" class="form-control"></td>
                <td><input type="number" id="volume2-grushavaiTransfer-5" class="form-control"></td>
            </tr>
        </tbody>
    </table> -->

    <button class="btn btn-success" onclick="saveData(1)">Сохранить</button>
<div>
<!-- <script>
    function calculate() {
    // Получаем значения из input-полей
    const pkop = parseFloat(document.getElementById("pkop").value) || 0;
    const zhanazholedit = parseFloat(document.getElementById("zhanazholedit").value) || 0;

    // Получаем проценты из таблицы
    const psp45PP = parseFloat(document.getElementById("psp45PP").value) || 0;
    const zhanazholPP = parseFloat(document.getElementById("zhanazholPP").value) || 0;
    const kenkiyakTransferPP = parseFloat(document.getElementById("kenkiyakTransferPP").value) || 0;
    const kenkiyakPP = parseFloat(document.getElementById("kenkiyakPP").value) || 0;
    const pkopPP = parseFloat(document.getElementById("pkopPP").value) || 0;

    // Расчеты
    const pkopP = Math.round(pkopPP * (pkop / 100));
    const kumkol = pkop + pkopP;
    const kenkiyakP = Math.round(kenkiyakPP * (kumkol / 100));
    const kenkiyak = kumkol + kenkiyakP;
    const kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    const kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    const zhanazholP = Math.round(zhanazholPP * (zhanazholedit / 100));
    const zhanazhol = zhanazholedit + zhanazholP;
    const psp45first = kenkiyakTransfer - zhanazhol;
    const pspP = Math.round(psp45PP * (psp45first / 100));
    const psp45end = psp45first + pspP;

    // Заполняем таблицу результатами
    document.getElementById("psp45end").value = psp45end;
    document.getElementById("pspP").value = pspP;
    document.getElementById("psp45first").value = psp45first;

    document.getElementById("zhanazhol").value = zhanazhol;
    document.getElementById("zhanazholP").value = zhanazholP;

    document.getElementById("kenkiyakTransfer").value = kenkiyakTransfer;
    document.getElementById("kenkiyakTransferP").value = kenkiyakTransferP;
    document.getElementById("kenkiyak-first").value = kenkiyak;
    document.getElementById("kenkiyak-second").value = kenkiyak;

    document.getElementById("kumkol-first").value = kumkol;
    document.getElementById("kumkol-second").value = kumkol;


    document.getElementById("kenkiyakP").value = kenkiyakP;
    
    document.getElementById("pkopP").value = pkopP;

    console.log("Расчет завершен!");
    }


    </script> -->
<!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
    let inputs = document.querySelectorAll("input");

    // Добавляем обработчик на все input'ы
    inputs.forEach(input => {
        input.addEventListener("input", function () {
            calculate();
        });
    });

    calculate(); // Первичный расчёт при загрузке страницы
    });

    function calculate() {
    // Получаем значения из input-полей
    const pkop = parseFloat(document.getElementById("pkop").value) || 0;
    const zhanazholedit = parseFloat(document.getElementById("zhanazholedit").value) || 0;

    // Получаем проценты из таблицы
    const psp45PP = parseFloat(document.getElementById("psp45PP").value) || 0;
    const zhanazholPP = parseFloat(document.getElementById("zhanazholPP").value) || 0;
    const kenkiyakTransferPP = parseFloat(document.getElementById("kenkiyakTransferPP").value) || 0;
    const kenkiyakPP = parseFloat(document.getElementById("kenkiyakPP").value) || 0;
    const pkopPP = parseFloat(document.getElementById("pkopPP").value) || 0;

    // Расчеты
    const pkopP = Math.round(pkopPP * (pkop / 100));
    const kumkol = pkop + pkopP;
    const kenkiyakP = Math.round(kenkiyakPP * (kumkol / 100));
    const kenkiyak = kumkol + kenkiyakP;
    const kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    const kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    const zhanazholP = Math.round(zhanazholPP * (zhanazholedit / 100));
    const zhanazhol = zhanazholedit + zhanazholP;
    const psp45first = kenkiyakTransfer - zhanazhol;
    const pspP = Math.round(psp45PP * (psp45first / 100));
    const psp45end = psp45first + pspP;

    // Заполняем таблицу результатами
    document.getElementById("psp45end").value = psp45end;
    document.getElementById("pspP").value = pspP;
    document.getElementById("psp45first").value = psp45first;
    document.getElementById("zhanazhol").value = zhanazhol;
    document.getElementById("zhanazholP").value = zhanazholP;
    document.getElementById("kenkiyakTransfer").value = kenkiyakTransfer;
    document.getElementById("kenkiyakTransferP").value = kenkiyakTransferP;
    document.getElementById("kenkiyak-first").value = kenkiyak;
    document.getElementById("kenkiyak-second").value = kenkiyak;
    document.getElementById("kumkol-first").value = kumkol;
    document.getElementById("kumkol-second").value = kumkol;
    document.getElementById("kenkiyakP").value = kenkiyakP;
    document.getElementById("pkopP").value = pkopP;
    }
</script> -->

<script >
    document.addEventListener("DOMContentLoaded", function () {
    let inputs = document.querySelectorAll("input");

    // Обрабатываем ввод: при потере фокуса или нажатии Enter пересчитываем
    inputs.forEach(input => {
        input.addEventListener("blur", function () {
            handleInput(this.id);
        });

        input.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                this.blur();
            }
        });
    });

    calculateFrom(); // Первичный расчёт при загрузке страницы
});

function handleInput(inputId) {
    if (["loss-pkopP", "loss-kenkiyakP", "loss-kenkiyakTransferP", "loss-pspP", "loss-zhanazholP"].includes(inputId)) {
        calculateFrom(inputId);
    } else {
        calculateFrom();
    }
}

function calculateFrom(startId = null) {
    // Получаем исходные значения
    let pkop = parseFloat(document.getElementById("volume2-pkop").value) || 0;
    let zhanazholedit = parseFloat(document.getElementById("volume2-zhanazholedit").value) || 0;

    // Получаем проценты
    let psp45PP = parseFloat(document.getElementById("percent-psp45PP").value) || 0;
    let zhanazholPP = parseFloat(document.getElementById("percent-zhanazholPP").value) || 0;
    let kenkiyakTransferPP = parseFloat(document.getElementById("percent-kenkiyakTransferPP").value) || 0;
    let kenkiyakPP = parseFloat(document.getElementById("percent-kenkiyakPP").value) || 0;
    let pkopPP = parseFloat(document.getElementById("percent-pkopPP").value) || 0;

    // Получаем сохранённые значения или вычисляем по формуле
    let pkopP = parseFloat(document.getElementById("loss-pkopP").value) || Math.round(pkopPP * (pkop / 100));
    let kumkol = pkop + pkopP;
    let kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || Math.round(kenkiyakPP * (kumkol / 100));
    let kenkiyak = kumkol + kenkiyakP;
    let kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    let kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    let zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || Math.round(zhanazholPP * (zhanazholedit / 100));
    let zhanazhol = zhanazholedit + zhanazholP;
    let psp45first = kenkiyakTransfer - zhanazhol;
    let pspP = parseFloat(document.getElementById("loss-pspP").value) || Math.round(psp45PP * (psp45first / 100));
    let psp45end = psp45first + pspP;

    // 🔹 Динамическое обновление значений по цепочке
    if (startId === "loss-pkopP") {
        pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
        kumkol = pkop + pkopP;
        kenkiyakP = Math.round(kenkiyakPP * (kumkol / 100));
        kenkiyak = kumkol + kenkiyakP;
        kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-kenkiyakP") {
        pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
        kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
        kenkiyak = kumkol + kenkiyakP;
        kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-kenkiyakTransferP") {
        pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
        kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
        kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || 0;
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-zhanazholP") {
        zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || 0;
        zhanazhol = zhanazholedit + zhanazholP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-pspP") {
        pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
        kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
        kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || 0;
        pspP = parseFloat(document.getElementById("loss-loss-pspP").value) || 0;
        psp45end = psp45first + pspP;
    }

    // 🔹 Заполняем только изменённые значения
    document.getElementById("volume-psp45end").value = psp45end;
    document.getElementById("loss-pspP").value = pspP;
    document.getElementById("volume2-psp45first").value = psp45first;
    document.getElementById("volume-zhanazhol").value = zhanazhol;
    document.getElementById("loss-zhanazholP").value = zhanazholP;
    document.getElementById("volume-kenkiyakTransfer").value = kenkiyakTransfer;
    document.getElementById("loss-kenkiyakTransferP").value = kenkiyakTransferP;
    document.getElementById("volume-kenkiyak").value = kenkiyak;
    document.getElementById("volume2-kenkiyak").value = kenkiyak;
    document.getElementById("volume-kumkol").value = kumkol;
    document.getElementById("volume2-kumkol").value = kumkol;
    document.getElementById("loss-kenkiyakP").value = kenkiyakP;
    document.getElementById("loss-pkopP").value = pkopP;
}
</script>


    <script>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/language.js"></script>
    <script src="js/Settings.js"></script>
    <script src="js/menu.js"></script>
</body>
</html>

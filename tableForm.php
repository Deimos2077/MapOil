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
    

<h3 class="mb-4">КНР</h3>
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
            <td><input type="number" id="percent-psp45PP" class="form-control" step="0.0001" value="0.0332"></td>
            <td><input type="number" id="volume-psp45end2" class="form-control"></td>
            <td><input type="number" id="loss-psp2P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first2" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol2" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol2P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit2" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="1" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer2" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer2P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak2" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyakPP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak2" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak2P" class="form-control"></td>
                <td><input type="number" id="volume2-kumkol2" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="15" data-from-id="4" data-to-id="14">
                <td>ГНПС Кумколь</td>
                <td>ГНПС им. Б. Джумагалиева</td>
                <td><input type="number" id="percent-kumkolPP" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kumkol2" class="form-control"></td>
                <td><input type="number" id="loss-kumkol2P" class="form-control"></td>
                <td><input type="number" id="volume2-dzhumagalieva" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="13" data-from-id="14" data-to-id="2">
                <td>ГНПС им. Б. Джумагалиева</td>
                <td>ГНПС Атасу</td>
                <td><input type="number" id="percent-dzhumagalievaPP" class="form-control" step="0.0001" value="0.0754"></td>
                <td><input type="number" id="volume-dzhumagalieva" class="form-control"></td>
                <td><input type="number" id="loss-dzhumagalievaP" class="form-control"></td>
                <td><input type="number" id="volume2-atasuTransfer" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="26" data-to-id="26">
                <td colspan="2">ГНПС Атасу (перевалка в н/п Атасу -Алашанькоу)</td>
                <td><input type="number" id="percent-atasuTransferPP" class="form-control" step="0.0001" value="0.0051"></td>
                <td><input type="number" id="volume-atasuTransfer" class="form-control"></td>
                <td><input type="number" id="loss-atasuTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-atasu" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="11" data-from-id="2" data-to-id="1">
                <td>ГНПС Атасу</td>
                <td>Алашанькоу</td>
                <td><input type="number" id="percent-alashankouPP" class="form-control" step="0.0001" value="0.0965"></td>
                <td><input type="number" id="volume-alashankou" class="form-control main-input"></td>
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
            <td><input type="number" id="volume-psp45end3" class="form-control"></td>
            <td><input type="number" id="loss-psp3P" class="form-control"></td>
            <td><input type="number" id="volume2-psp45first3" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazholPP" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol3" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol3P" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazholedit3" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="1" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyakTransferPP" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyakTransfer3" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyakTransfer3P" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak3" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="7">
                <td>ГНПС Кенкияк</td>
                <td>НПС им. Шманова</td>
                <td><input type="number" id="percent-shmanovaPP" class="form-control" step="0.0001" value="0.0429"></td>
                <td><input type="number" id="volume-kenkiyak3" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak3P" class="form-control"></td>
                <td><input type="number" id="volume2-shmanova" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="7" data-to-id="19">
                <td>НПС им. Шманова</td>
                <td>НПС им. Касымова</td>
                <td><input type="number" id="percent-kasimovaPP" class="form-control" step="0.0001" value="0.0455"></td>
                <td><input type="number" id="volume-shmanova" class="form-control"></td>
                <td><input type="number" id="loss-shmanovaP" class="form-control"></td>
                <td><input type="number" id="volume2-kasimova" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="20" data-from-id="19" data-to-id="24">
                <td>НПС им. Касымова</td>
                <td>1235,3 км граница (РК/РФ)</td>
                <td><input type="number" id="percent-km1235PP" class="form-control" step="0.0001" value="0.1122"></td>
                <td><input type="number" id="volume-kasimova" class="form-control"></td>
                <td><input type="number" id="loss-kasimovaP" class="form-control"></td>
                <td><input type="number" id="volume2-km1235" class="form-control main-input"></td>
            </tr>
            <tr data-pipeline-id="23" data-from-id="24" data-to-id="8">
                <td>1235,3 км граница (РК/РФ)</td>
                <td>Самара</td>
                <td><input type="number" id="percent-samaraPP" class="form-control" step="0.0001" value="0.0192"></td>
                <td><input type="number" id="volume-km1235" class="form-control"></td>
                <td><input type="number" id="loss-samaraP" class="form-control"></td>
                <td><input type="number" id="volume2-samara" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="1" data-from-id="27" data-to-id="27">
                <td colspan="2">СамараСамара БСН (на Дружбу) (перевалка)</td>
                <td><input type="number" id="percent-samaraTransferPP" class="form-control" step="0.0001" value="0.0137"></td>
                <td><input type="number" id="volume-samara" class="form-control"></td>
                <td><input type="number" id="loss-samaraTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-samaraTransfer" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="2" data-from-id="8" data-to-id="16">
                <td>Самара</td>
                <td>Клин</td>
                <td><input type="number" id="percent-klinPP" class="form-control" step="0.0001" value="0.0000"></td>
                <td><input type="number" id="volume-samaraTransfer" class="form-control"></td>
                <td><input type="number" id="loss-klinP" class="form-control"></td>
                <td><input type="number" id="volume2-klin" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="3" data-from-id="16" data-to-id="20">
                <td>Клин</td>
                <td>Никольское</td>
                <td><input type="number" id="percent-nikolskiPP" class="form-control" step="0.0001" value="0.0065"></td>
                <td><input type="number" id="volume-klin" class="form-control"></td>
                <td><input type="number" id="loss-nikolskiP" class="form-control"></td>
                <td><input type="number" id="volume2-nikolski" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="4" data-from-id="20" data-to-id="23">
                <td>Никольское</td>
                <td>Унеча (на Андреаполь)</td>
                <td><input type="number" id="percent-unechaPP" class="form-control" step="0.0001" value="0.0458"></td>
                <td><input type="number" id="volume-nikolski" class="form-control"></td>
                <td><input type="number" id="loss-unechaP" class="form-control"></td>
                <td><input type="number" id="volume2-unecha" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="22" data-from-id="23" data-to-id="10">
                <td colspan="2">НБ Усть-Луга (перевалка)</td>
                <td><input type="number" id="percent-ustlugaTransferPP" class="form-control" step="0.0001" value="0.0258"></td>
                <td><input type="number" id="volume-unecha" class="form-control"></td>
                <td><input type="number" id="loss-ustlugaTransferP" class="form-control"></td>
                <td><input type="number" id="volume2-ustlugaTransfer" class="form-control"></td>
            </tr>
        </tbody>
    </table>
<!-- 
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
    </table>

    <button class="btn btn-success" onclick="saveData(1)">Сохранить</button>
<div> -->





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
    <script src="js/calculate.js">
        
    </script>
</body>
</html>

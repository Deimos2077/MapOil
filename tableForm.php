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


        const tables = document.querySelectorAll("table");
        let data = [];

        tables.forEach(table => {
            const pipelinesSystemId = table.getAttribute("data-pipelines-system-id");
            const rows = table.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const pipelineId = row.getAttribute("data-pipeline-id");
                const fromPointId = row.getAttribute("data-from-id");
                const toPointId = row.getAttribute("data-to-id");

                // Получаем input и приводим к числу
                const getNumber = (selector) => {
                    let input = row.querySelector(selector);
                    if (!input || input.value.trim() === "") return 0;  // Если пусто → 0
                    let value = input.value.replace(',', '.'); // Меняем запятую на точку
                    return isNaN(parseFloat(value)) ? 0 : parseFloat(value);
                };

                const lossCoefficient = getNumber("[id^='percent-']");
                const fromAmount = getNumber("[id^='volume-']");
                const losses = getNumber("[id^='loss-']");
                const toAmount = getNumber("[id^='volume2-']");

                data.push({
                    date: date,
                    pipeline_id: parseInt(pipelineId),
                    piplines_system_id: parseInt(pipelinesSystemId),
                    from_point_id: parseInt(fromPointId),
                    to_point_id: parseInt(toPointId),
                    loss_coefficient: lossCoefficient,
                    from_amount: fromAmount,
                    losses: losses,
                    to_amount: toAmount
                });
            });
        });

        // Логируем перед отправкой
        console.log("Отправляемые данные:", JSON.stringify(data, null, 2));

        try {
            const response = await fetch('save_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                alert("Данные успешно сохранены!");
            } else {
                alert("Ошибка: " + result.message);
            }
        } catch (error) {
            alert("Ошибка при отправке данных: " + error);
        }
    }
</script>




</head>
<body class="container mt-4">
<nav id="slide-menu">
    <ul>
        <li class="timeline"><a class="menu-href" href="http://localhost/oilgraf/" data-i18n="menu_graphs">Графики</a></li>
        <li class="events"><a class="menu-href" href="/project/MapOil/table.php" data-i18n="menu_reports">МатОтчет</a></li>
        <li class="timeline"><a class="menu-href" href="/project/MapOil/map.php" data-i18n="menu_map">Карта</a></li>
        <li class="calculator"><a class="menu-href" href="/project/MapOil/calculator.php" data-i18n="menu_calculator">"Калькулятор"</a></li>
        <li class="svg-editor"><a class="menu-href" href="/project/svgedit-master/dist/editor/" target="_blank">Редактор SVG</a></li>
        <li class="settings"><a href="#" id="settings-toggle" data-i18n="menu_settings">Настройки</a></li>
        <li class="logout"><a href="logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>
<div id="content">
<div class="menu-trigger"></div>

    <h2 class="mb-4">Форма расчета потерь нефти</h2>
    <label style="display:block" for="date-input">Дата:</label>
    <input type="date" id="date-input" class="form-control mb-3">

    <h3 class="mb-4">Внутренний рынок (ПКОП)</h3>
    <table class="table table-bordered" data-pipelines-system-id="1">
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
            <td><input type="number" id="percent-psp45-1" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45-1" class="form-control"></td>
            <td><input type="number" id="loss-psp45-1" class="form-control"></td>
            <td><input type="number" id="volume2-psp45-1" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="17" data-from-id="11" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazhol-1" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol-1" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol-1" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazhol-1" class="form-control"></td>
        </tr>
            <tr data-pipeline-id="0" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyak-transfer-1" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-1" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-1" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-1" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyak-1" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak-1" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-1" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-1" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="14" data-from-id="4" data-to-id="6">
                <td>ГНПС Кумколь</td>
                <td>ПСП ПКОП</td>
                <td><input type="number" id="percent-pkop-1" class="form-control" step="0.0001" value="0.1818"></td>
                <td><input type="number" id="volume-pkop-1" class="form-control"></td>
                <td><input type="number" id="loss-pkop-1" class="form-control"></td>
                <td><input type="number" id="volume2-pkop-1 " class="form-control main-input"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">КНР</h3>
    <table class="table table-bordered" data-pipelines-system-id="2">
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
            <tr data-pipeline-id="0" data-from-id="25" data-to-id="25">
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
            <tr data-pipeline-id="0" data-from-id="26" data-to-id="26">
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
    <table class="table table-bordered" data-pipelines-system-id="3">
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
            <tr data-pipeline-id="0" data-from-id="25" data-to-id="25">
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
            <tr data-pipeline-id="0" data-from-id="27" data-to-id="27">
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
    <table class="table table-bordered" data-pipelines-system-id="4">
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
            <tr data-pipeline-id="0" data-from-id="25" data-to-id="25">
                <td colspan="2">ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="percent-kenkiyak-transfer-4" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-4" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-4" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-4" class="form-control"></td>
            </tr>
            <tr data-pipeline-id="19" data-from-id="5" data-to-id="4">
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyak-1" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak-1" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-1" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-1" class="form-control main-input"></td>
            </tr>
        </tbody>
    </table>

    <h3 class="mb-4">Европа (Новороссийск)</h3>
    <table class="table table-bordered" data-pipelines-system-id="5">
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
            <tr data-pipeline-id="0" data-from-id="27" data-to-id="27">
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
<div>
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

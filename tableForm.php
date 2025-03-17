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
    <style>
        .main-input {
            background-color: #ffcccc !important;
            border: 2px solid red;
            font-weight: bold;
        }
        #date-input{
            width: 17%;
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
    <label for="date-input">Дата:</label>
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
        <tr data-pipeline-id="1" data-from-id="2" data-to-id="3">
            <td>ПСП 45 км</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-psp45-1" class="form-control" step="0.0001" value="0.332"></td>
            <td><input type="number" id="volume-psp45-1" class="form-control"></td>
            <td><input type="number" id="loss-psp45-1" class="form-control"></td>
            <td><input type="number" id="volume2-psp45-1" class="form-control"></td>
        </tr>
        <tr data-pipeline-id="2" data-from-id="4" data-to-id="5">
            <td>КПОУ Жанажол</td>
            <td>ГНПС Кенкияк</td>
            <td><input type="number" id="percent-zhanazhol-1" class="form-control" step="0.0001" value="0.0377"></td>
            <td><input type="number" id="volume-zhanazhol-1" class="form-control"></td>
            <td><input type="number" id="loss-zhanazhol-1" class="form-control"></td>
            <td><input type="number" id="volume2-zhanazhol-1" class="form-control"></td>
        </tr>
            <!-- <tr>
                <td>ГНПС Кенкияк (перевалка)</td>
                <td>ГНПС Кенкияк</td>
                <td><input type="number" id="percent-kenkiyak-transfer-1" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="number" id="volume-kenkiyak-transfer-1" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-transfer-1" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-transfer-1" class="form-control"></td>
            </tr>
            <tr>
                <td>ГНПС Кенкияк</td>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="percent-kenkiyak-1" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="number" id="volume-kenkiyak-1" class="form-control"></td>
                <td><input type="number" id="loss-kenkiyak-1" class="form-control"></td>
                <td><input type="number" id="volume2-kenkiyak-1" class="form-control"></td>
            </tr>
            <tr>
                <td>ГНПС Кумколь</td>
                <td>ПСП ПКОП</td>
                <td><input type="number" id="percent-pkop-1" class="form-control" step="0.0001" value="0.1818"></td>
                <td><input type="number" id="volume-pkop-1" class="form-control"></td>
                <td><input type="number" id="loss-pkop-1" class="form-control"></td>
                <td><input type="number" id="volume2-pkop-1 " class="form-control main-input"></td>
            </tr> -->
        </tbody>
    </table>
    <button class="btn btn-success" onclick="saveData(1)">Сохранить</button>
<div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/language.js"></script>
    <script src="js/Settings.js"></script>
    <script src="js/menu.js"></script>
</body>
</html>

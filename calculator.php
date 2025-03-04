<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохранение данных о потерях нефти</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-input {
            background-color: #ffcccc !important;
            border: 2px solid red;
            font-weight: bold;
        }
    </style>
    <script>
        async function saveData(pipelineId) {
            const date = document.getElementById("date-input").value;
            if (!date) {
                alert("Пожалуйста, выберите дату.");
                return;
            }

            const data = {
                date: date,
                losses: [
                    { id: 12, volume: document.getElementById(`volume-psp45-${pipelineId}`).value, percent: document.getElementById(`percent-psp45-${pipelineId}`).value, loss: document.getElementById(`loss-psp45-${pipelineId}`).value },
                    { id: 5, volume: document.getElementById(`volume-kenkiyak-${pipelineId}`).value, percent: document.getElementById(`percent-kenkiyak-${pipelineId}`).value, loss: document.getElementById(`loss-kenkiyak-${pipelineId}`).value },
                    { id: 25, volume: document.getElementById(`volume-kenkiyak-transfer-${pipelineId}`).value, percent: document.getElementById(`percent-kenkiyak-transfer-${pipelineId}`).value, loss: document.getElementById(`loss-kenkiyak-transfer-${pipelineId}`).value },
                    { id: 4, volume: document.getElementById(`volume-kumkol-${pipelineId}`).value, percent: document.getElementById(`percent-kumkol-${pipelineId}`).value, loss: document.getElementById(`loss-kumkol-${pipelineId}`).value },
                    { id: 6, volume: document.getElementById(`volume-pkop-${pipelineId}`).value, percent: document.getElementById(`percent-pkop-${pipelineId}`).value, loss: document.getElementById(`loss-pkop-${pipelineId}`).value }
                ]
            };

            let response = await fetch("save_data.php?pipeline_id=" + pipelineId, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            let result = await response.text();
            alert(result);
        }

        function calculateLoss(sourceId, pipelineId) {
        let volume = parseFloat(document.getElementById(`volume-${sourceId}-${pipelineId}`).value) || 0;
        let percent = parseFloat(document.getElementById(`percent-${sourceId}-${pipelineId}`).value) || 0;
        let loss = Math.round((volume * percent) / 100);
        document.getElementById(`loss-${sourceId}-${pipelineId}`).value = loss;
        return Math.round(volume / (1 - percent / 100));
    }

    function calculateAll(pipelineId) {
        let lastVolumeId = pipelineId === "1" ? "pkop" : "atasu-alashankoy";

        let lastVolume = parseFloat(document.getElementById(`volume-${lastVolumeId}-${pipelineId}`).value) || 0;
        document.getElementById(`loss-${lastVolumeId}-${pipelineId}`).value = Math.round((lastVolume * parseFloat(document.getElementById(`percent-${lastVolumeId}-${pipelineId}`).value)) / 100);

        let previousId = lastVolumeId;
        let sources = pipelineId === "1"
            ? ["kumkol", "kenkiyak-transfer", "kenkiyak", "psp45"]
            : ["atasu-transfer", "atasu", "dzhumagalieva", "kumkol", "kenkiyak-transfer", "kenkiyak", "psp45"];

        for (let source of sources) {
            let calculatedVolume = calculateLoss(previousId, pipelineId);
            document.getElementById(`volume-${source}-${pipelineId}`).value = calculatedVolume;
            previousId = source;
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".main-input").forEach(input => {
            input.addEventListener("input", function () {
                let pipelineId = this.id.split("-").pop();
                calculateAll(pipelineId);
            });
        });
    });
    </script>
</head>
<body class="container mt-4">
    <h2 class="mb-4">Форма расчета потерь нефти</h2>
    <label for="date-input">Дата:</label>
    <input type="date" id="date-input" class="form-control mb-3">

    <h3 class="mb-4">Трубопровод 1</h3>
    <table class="table table-bordered">
        <thead>
            <tr class="table-primary">
                <th>Источник</th>
                <th>Объем нефти (тонн)</th>
                <th>Процент потерь (%)</th>
                <th>Потери (тонн)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ПСП 45 км</td>
                <td><input type="number" id="volume-psp45-1" class="form-control" readonly></td>
                <td><input type="number" id="percent-psp45-1" class="form-control" step="0.0001" value="0"></td>
                <td><input type="text" id="loss-psp45-1" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Кенкияк</td>
                <td><input type="number" id="volume-kenkiyak-1" class="form-control" readonly></td>
                <td><input type="number" id="percent-kenkiyak-1" class="form-control" step="0.0001" value="0.0332"></td>
                <td><input type="text" id="loss-kenkiyak-1" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="volume-kenkiyak-transfer-1" class="form-control" readonly></td>
                <td><input type="number" id="percent-kenkiyak-transfer-1" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="text" id="loss-kenkiyak-transfer-1" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="volume-kumkol-1" class="form-control" readonly></td>
                <td><input type="number" id="percent-kumkol-1" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="text" id="loss-kumkol-1" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ПСП ПКОП</td>
                <td><input type="number" id="volume-pkop-1" class="form-control main-input"></td>
                <td><input type="number" id="percent-pkop-1" class="form-control" step="0.0001" value="0.1818"></td>
                <td><input type="text" id="loss-pkop-1" class="form-control" readonly></td>
            </tr>
        </tbody>
    </table>
    <button class="btn btn-success" onclick="saveData(1)">Сохранить</button>

    <h3 class="mb-4 mt-5">Трубопровод 2</h3>
    <table class="table table-bordered">
        <tbody>
        <tr>
                <td>ПСП 45 км</td>
                <td><input type="number" id="volume-psp45-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-psp45-2" class="form-control" step="0.0001" value="0"></td>
                <td><input type="text" id="loss-psp45-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Кенкияк</td>
                <td><input type="number" id="volume-kenkiyak-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-kenkiyak-2" class="form-control" step="0.0001" value="0.0332"></td>
                <td><input type="text" id="loss-kenkiyak-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Кенкияк (перевалка)</td>
                <td><input type="number" id="volume-kenkiyak-transfer-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-kenkiyak-transfer-2" class="form-control" step="0.0001" value="0.0077"></td>
                <td><input type="text" id="loss-kenkiyak-transfer-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Кумколь</td>
                <td><input type="number" id="volume-kumkol-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-kumkol-2" class="form-control" step="0.0001" value="0.0794"></td>
                <td><input type="text" id="loss-kumkol-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС им. Б. Джумагалиева</td>
                <td><input type="number" id="volume-dzhumagalieva-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-dzhumagalieva-2" class="form-control" step="0.0001" value="0.0525"></td>
                <td><input type="text" id="loss-dzhumagalieva-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Атасу</td>
                <td><input type="number" id="volume-atasu-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-atasu-2" class="form-control" step="0.0001" value="0.0724"></td>
                <td><input type="text" id="loss-atasu-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>ГНПС Атасу (перевалка в н/п Атасу -Алашанькоу)</td>
                <td><input type="number" id="volume-atasu-transfer-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-atasu-transfer-2" class="form-control" step="0.0001" value="0.0051"></td>
                <td><input type="text" id="loss-atasu-transfer-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>Атасу -Алашанькоу</td>
                <td><input type="number" id="volume-atasu-alashankoy-2" class="form-control main-input"></td>
                <td><input type="number" id="percent-atasu-alashankoy-2" class="form-control" step="0.0001" value="0.0965"></td>
                <td><input type="text" id="loss-atasu-alashankoy-2" class="form-control" readonly></td>
            </tr>
            <tr>
                <td>Алашанькоу</td>
                <td><input type="number" id="volume-alashankoy-2" class="form-control" readonly></td>
                <td><input type="number" id="percent-alashankoy-2" class="form-control" step="0.0001" value="0.0"></td>
                <td><input type="text" id="loss-alashankoy-2" class="form-control" readonly></td>
            </tr>
        </tbody>
    </table>
    <button class="btn btn-success" onclick="saveData(2)">Сохранить</button>
</body>
</html>

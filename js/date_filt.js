document.addEventListener("DOMContentLoaded", async function () {
    console.log("📅 Инициализация фильтра...");

    const monthInput = document.getElementById('month-input');
    if (!monthInput) {
        console.error("❌ Элемент #month-input не найден");
        return;
    }

    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = String(now.getMonth() + 1).padStart(2, '0');
    const monthStr = `${currentYear}-${currentMonth}`;

    monthInput.value = monthStr;

    const [year, month] = monthStr.split('-');
    await updateMapData(year, month);

    monthInput.addEventListener('change', async () => {
        const [selectedYear, selectedMonth] = monthInput.value.split('-');
        await updateMapData(selectedYear, selectedMonth);
    });
});


async function updateMapData(year, month) {
    console.log(`📊 Загружаем данные за ${year}-${month}`);

    const points = await fetchPointsFromDB();
    const oilTransferData = await fetchOilTransferFromDB(year, month);
    const reservoirs = await fetchReservoirVolumesFromDB(year, month);

    // Очищаем все слои (включая стрелки, резервуары и подписи)
    clearAllDataLayers();

    // Проверка: если нет данных — не отображаем ничего
    if (oilTransferData.length === 0 && reservoirs.length === 0) {
        console.warn("⚠ Нет данных за выбранный месяц. Карта очищена.");
        dataLoaded = false;

        // Показываем уведомление (если есть элемент)
        const msg = document.getElementById('no-data-message');
        if (msg) msg.style.display = 'block';

        return;
    }

    // Скрываем уведомление, если ранее было показано
    const msg = document.getElementById('no-data-message');
    if (msg) msg.style.display = 'none';

    // Визуализация
    if (oilTransferData.length > 0) {
        addMinimalistFlow(points, oilTransferData);
        await displayKenkiyakOilTotal(year, month, points); // если нужно
    }

    if (reservoirs.length > 0) {
        addReservoirs(reservoirs);
    }

    dataLoaded = true; // Только если данные реально отобразились
    console.log("✅ Карта обновлена");
}


function clearAllDataLayers() {
    flowLayerGroup.clearLayers();
    minimalistFlowLayerGroup.clearLayers();
    reservoirLayerGroup.clearLayers();
    pointTanksLayer.clearLayers();
    technicalTanksLayer.clearLayers();
}



async function fetchReservoirVolumesFromDB(year, month) {
    try {
        const response = await fetch(`database/getReservoirData.php?year=${year}&month=${month}`);
        if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

        const reservoirData = await response.json();

        if (!Array.isArray(reservoirData)) {
            console.error("❌ Ошибка: данные резервуаров не массив!", reservoirData);
            return [];
        }

        console.log(`📊 Объединённые данные о резервуарах (${year}-${month}):`, reservoirData);
        return reservoirData;
    } catch (error) {
        console.error("❌ Ошибка загрузки резервуаров:", error);
        return [];
    }
}



// Получаем последнюю доступную дату из базы
async function getLatestAvailableMonth() {
    try {
        const response = await fetch('database/getAvailableDates.php');
        if (!response.ok) throw new Error("Ошибка загрузки дат");

        const dates = await response.json();
        if (dates.length === 0) return null;

        const latest = dates[0]; // Самая новая дата
        return {
            year: latest.year,
            month: latest.month
        };
    } catch (error) {
        console.error("❌ Ошибка при получении последнего месяца:", error);
        return null;
    }
}

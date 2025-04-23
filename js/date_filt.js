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
        showPreloader(); // ⏳ Включаем прелоадер

        try {
            console.log(`📊 Загружаем данные за ${year}-${month}`);

            // ⬇ Загружаем параллельно
            const [points, oilTransferData, reservoirs] = await Promise.all([
                fetchPointsFromDB(),
                fetchOilTransferFromDB(year, month),
                fetchReservoirVolumesFromDB(year, month)
            ]);

            clearAllDataLayers();

            const zoomThreshold = 6;
            const currentZoom = map.getZoom();

            const checkboxOne = document.getElementById('checkboxOne');
            const checkboxTwo = document.getElementById('checkboxTwo');

            // ⛔ Нет данных
            if (oilTransferData.length === 0 && reservoirs.length === 0) {
                console.warn("⚠ Нет данных за выбранный месяц. Карта очищена.");
                dataLoaded = false;

                const msg = document.getElementById('no-data-message');
                if (msg) msg.style.display = 'block';

                return;
            }

            // ✅ Скрыть сообщение об отсутствии данных
            const msg = document.getElementById('no-data-message');
            if (msg) msg.style.display = 'none';

            // 🧠 Кэшируем
            window.cachedPoints = points;
            window.cachedOilTransferData = oilTransferData;
            window.cachedReservoirs = reservoirs;

            // 🧭 Линии всегда отрисовываются
            await main(points, oilTransferData);

            // 🛢️ Отрисовка нефти
            if (checkboxOne?.checked && oilTransferData.length > 0) {
                if (currentZoom >= zoomThreshold) {
                    addMinimalistFlow(points, oilTransferData);
                    await displayKenkiyakOilTotal(year, month, points);
                } else {
                    console.log("🛢️ Нефть не отрисована — зум ниже порога");
                }
            }

            // 🛢️ Отрисовка резервуаров
            if (checkboxTwo?.checked && reservoirs.length > 0) {
                if (currentZoom >= zoomThreshold) {
                    addReservoirs(reservoirs);
                } else {
                    console.log("🛢️ Резервуары не отрисованы — зум ниже порога");
                    clearReservoirLayers(); // очищаем
                }
            } else {
                clearReservoirLayers(); // отключено или нет данных
            }

            dataLoaded = true;
            console.log("✅ Карта обновлена");
        } catch (error) {
            console.error("❌ Ошибка при обновлении карты:", error);
        } finally {
            setTimeout(hidePreloader, 300); // ⌛ Плавное скрытие
        }
    }





    function clearAllDataLayers() {
        if (window.flowLayerGroup) flowLayerGroup.clearLayers();
        if (window.minimalistFlowLayerGroup) minimalistFlowLayerGroup.clearLayers();
        if (window.reservoirLayerGroup) reservoirLayerGroup.clearLayers();
        if (window.pointTanksLayer) pointTanksLayer.clearLayers();
        if (window.technicalTanksLayer) technicalTanksLayer.clearLayers();
    }

    function clearReservoirLayers() {
        if (window.reservoirLayerGroup) reservoirLayerGroup.clearLayers();
        if (window.pointTanksLayer) pointTanksLayer.clearLayers();
        if (window.technicalTanksLayer) technicalTanksLayer.clearLayers();
        console.log("🧹 Резервуары очищены");
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

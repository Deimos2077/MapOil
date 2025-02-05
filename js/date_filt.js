document.addEventListener("DOMContentLoaded", async function () {
    console.log("📅 Фильтрация данных загружается...");

    // Загружаем доступные даты при загрузке страницы
    await fetchAvailableDates();

    const filterButton = document.getElementById('filter-button');
    if (!filterButton) {
        console.error("❌ Ошибка: кнопка фильтрации не найдена в DOM!");
        return;
    }

    filterButton.addEventListener('click', async () => {
        const selectedYear = document.getElementById('year-select').value;
        const selectedMonth = document.getElementById('month-select').value;

        if (!selectedYear || !selectedMonth) {
            alert("⚠ Пожалуйста, выберите год и месяц!");
            return;
        }

        console.log(`📅 Фильтрация данных на ${selectedYear}-${selectedMonth}`);
        await updateMapData(selectedYear, selectedMonth);
    });
});

// Функция загрузки доступных дат (годов и месяцев)
async function fetchAvailableDates() {
    try {
        const response = await fetch('database/getAvailableDates.php'); // PHP-скрипт для получения годов и месяцев
        if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

        const dates = await response.json();
        
        const yearSelect = document.getElementById('year-select');
        const monthSelect = document.getElementById('month-select');

        if (!yearSelect || !monthSelect) {
            console.error("❌ Ошибка: селекторы года и месяца не найдены!");
            return;
        }

        // Очищаем списки перед обновлением
        yearSelect.innerHTML = "";
        monthSelect.innerHTML = "";

        // Добавляем уникальные годы
        [...new Set(dates.map(date => date.year))].forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        });

        // Добавляем уникальные месяцы
        [...new Set(dates.map(date => date.month))].forEach(month => {
            const option = document.createElement('option');
            option.value = month;
            option.textContent = month;
            monthSelect.appendChild(option);
        });

    } catch (error) {
        console.error('❌ Ошибка загрузки доступных дат:', error);
    }
}

// Функция обновления карты с фильтром по дате
async function updateMapData(year, month) {
    console.log(`📊 Обновляем данные карты на ${year}-${month}`);

    const points = await fetchPointsFromDB();
    const oilTransferData = await fetchOilTransferFromDB(year, month);
    const reservoirs = await fetchReservoirVolumesFromDB(year, month);

    if (points.length === 0 || oilTransferData.length === 0) {
        console.warn("⚠ Недостаточно данных для отображения.");
        return;
    }

    // Очищаем карту перед добавлением новых данных
    flowLayerGroup.clearLayers();

    // Обновляем данные
    addMinimalistFlow(points, oilTransferData);
    addReservoirs(reservoirs);

    console.log("✅ Карта обновлена");
}

// Функция загрузки данных о нефти с учетом фильтрации по году и месяцу
async function fetchOilTransferFromDB(year, month) {
    try {
        const response = await fetch(`database/getData.php?table=oiltransfer&year=${year}&month=${month}`);
        if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

        const oilTransferData = await response.json();

        if (!Array.isArray(oilTransferData)) {
            console.error("❌ Ошибка: данные о нефти не массив!", oilTransferData);
            return [];
        }

        console.log(`📊 Данные о нефти (${year}-${month}):`, oilTransferData);
        
        return oilTransferData.map(record => ({
            id: record.id,
            from_point: record.from_point_id,
            to_point: record.to_point_id,
            from_amount: record.from_amount,
            to_amount: record.to_amount,
            losses: record.losses || 0
        }));
    } catch (error) {
        console.error("❌ Ошибка загрузки данных о нефти:", error);
        return [];
    }
}

// Функция загрузки данных резервуаров с учетом фильтрации
async function fetchReservoirVolumesFromDB(year, month) {
    try {
        const response = await fetch(`database/getData.php?table=reservoirvolumes&year=${year}&month=${month}`);
        if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

        const reservoirVolumes = await response.json();

        if (!Array.isArray(reservoirVolumes)) {
            console.error("❌ Ошибка: данные резервуаров не массив!", reservoirVolumes);
            return [];
        }

        console.log(`📊 Данные о резервуарах (${year}-${month}):`, reservoirVolumes);
        return reservoirVolumes;
    } catch (error) {
        console.error("❌ Ошибка загрузки данных резервуаров:", error);
        return [];
    }
}

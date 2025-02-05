        // Инициализация карты
        var map = L.map('map', {
            center: [51.5, 57], // Центр карты
            zoom: 5.55,          // Начальный зум
            minZoom: 4,       // Минимальный зум
            maxZoom: 10,      // Максимальный зум
            zoomSnap: 0.001,    // Шаг зума: 0.1 для более точного контроля
            zoomDelta: 0.001,   // Шаг зума при использовании колесика мыши или клавиш
            zoomControl: false // Отключение стандартных кнопок зума
            });
            

        // Подключение OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attributionControl: false
        }).addTo(map);
    
        // Подключение библиотеки Leaflet.easyPrint
        var printer = L.easyPrint({
            title: 'Распечатать карту',
            position: 'topleft', // Базовое положение в верхнем левом углу
            sizeModes: ['A4Portrait', 'A4Landscape'],
            exportOnly: false, // Печать напрямую
            hideControlContainer: false,
            customLayout: true,
            scale: 1
        }).addTo(map);
        

    map.invalidateSize(); // Перерендеринг карты

        // Массивы для хранения ссылок на текстовые элементы
        const labels = [];
        const distanceLabels = [];
        const markers = [];

//---------------Границы--------------------------

// Общий стиль для всех GeoJSON
    const geoJsonStyle = {
        color: 'purple',  // Цвет обводки (например, оранжевый)
        weight: 3,  // Толщина линии
        opacity: 0.5,        // Прозрачность обводки
        fillOpacity: 0     // Убираем заливку (только обводка)
    };

// Загружаем и добавляем первый GeoJSON
    fetch('json/kz_0.json')
        .then(response => response.json())
        .then(data => {
        L.geoJSON(data, { style: geoJsonStyle }).addTo(map);
    });



//----------------------------------Подключение базы данных-------------------------------


// Функция для получения данных о трубопроводах
async function fetchPipelinesFromDB() {
    try {
        const response = await fetch('database/getData.php?table=Pipelines');
        if (!response.ok) {
            throw new Error(`Ошибка HTTP: ${response.status}`);
        }
        const pipelines = await response.json();
        console.log('Данные трубопроводов из базы:', pipelines);
        return pipelines.map(pipeline => ({
            from: pipeline.from_point_id,
            to: pipeline.to_point_id,
            company: pipeline.company
        }));
    } catch (error) {
        console.error('Ошибка загрузки данных о трубопроводах:', error);
        return [];
    }
}

// Подключение точек
async function fetchPointsFromDB() {
    try {
        const response = await fetch('database/getData.php?table=Points');
        if (!response.ok) {
            throw new Error(`Ошибка HTTP: ${response.status}`);
        }
        const points = await response.json();
        console.log('Данные точек из базы:', points);
        return points.map(point => ({
            id: point.id,
            name: point.name,
            coords: point.lat && point.lng ? [point.lat, point.lng] : null // Проверка координат
        }));
    } catch (error) {
        console.error('Ошибка загрузки данных о точках:', error);
        return [];
    }
}

// Функция для получения данных о передаче нефти
async function fetchOilTransferFromDB() {
    try {
        const response = await fetch('database/getData.php?table=oiltransfer');
        if (!response.ok) {
            throw new Error(`Ошибка HTTP: ${response.status}`);
        }
        const oilTransferData = await response.json();
        console.log('Данные о передаче нефти из базы:', oilTransferData);
        return oilTransferData.map(record => ({
            id: record.id,
            from_point: record.from_point_id,
            to_point: record.to_point_id,
            from_amount: record.from_amount,
            to_amount: record.to_amount,
            losses: record.losses || 0
        }));
    } catch (error) {
        console.error('Ошибка загрузки данных о передаче нефти:', error);
        return [];
    }
}

// Инициализация карты
async function initializeMap() {
    const points = await fetchPointsFromDB();
    const oilTransferData = await fetchOilTransferFromDB();

    if (points.length === 0 || oilTransferData.length === 0) {
        console.error('Недостаточно данных для отрисовки карты.');
        return;
    }

    addMinimalistFlow(points, oilTransferData);

    // Добавляем стили для меток
    const style = document.createElement('style');
    style.innerHTML = `
    .oil-label div {
        font-size: 12px;
        font-weight: bold;
        color: black;
        padding: 2px 5px;
        border-radius: 4px;
        text-align: center;
        white-space: nowrap;
    }
    `;
    document.head.appendChild(style);
}

// Вызов функции и инициализация карты
initializeMap();


//----------------------------------Точки(Метки) на карте-------------------------------------- 
map.createPane('pointsPane');
map.getPane('pointsPane').style.zIndex = 600; // Высокий zIndex для точек

fetch('database/getData.php?table=Points')
    .then(response => response.json())
    .then(points => {
        const zoomThreshold = 7; // Уровень зума, при котором названия меняются
        let markers = [];

        // Определяем разрешение экрана
        const screenWidth = window.innerWidth;
        const screenHeight = window.innerHeight;
        let labelOffsets, labelCloseOffsets;

        // Смещения для 1920x1080
        const labelOffsets_1920 = {
            "ПСП 45 км": { offsetLat: 0.15, offsetLng: 0 },
            "КПОУ Жана Жол": { offsetLat: -0.35, offsetLng: 0 },
            "НПС им. Шманова": { offsetLat: -0.25, offsetLng: 1.8 },
            "НПС им. Касымова": { offsetLat: -0.1, offsetLng: -2.1 },
            "Новороссийск": { offsetLat: 0.1, offsetLng: -1.4 },
            "Грушовая": { offsetLat: -0.15, offsetLng: 1.15 },
            "Унеча": { offsetLat: -0.25, offsetLng: 0 },
            "Никольское": { offsetLat: 0.15, offsetLng: 0 },
            "Алашанькоу": { offsetLat: -0.1, offsetLng: 1.4 },
            "ГНПС Атасу": { offsetLat: -0.1, offsetLng: 1.4 },
            "ПНХЗ": { offsetLat: 0.15, offsetLng: 0 },
            "ГНПС Кумколь": { offsetLat: -0.35, offsetLng: 0 },
            "ГНПС Кенкияк": { offsetLat: -0.1, offsetLng: 1.6 },
            "ПКОП": { offsetLat: -0.4, offsetLng: 0 },
            "ПСП Самара": { offsetLat: 0, offsetLng: 1.3 },
            "Усть-Луга": { offsetLat: -0.05, offsetLng: 1.1 },
            "Большая Черниговка": { offsetLat: 0.1, offsetLng: 1.15 },
            "ГНПС им. Б. Джумагалиева": { offsetLat: 0.2, offsetLng: 1.5 },
            "Клин": { offsetLat: 0.15, offsetLng: 0 },
            "915 км н/пр.КЛ": { offsetLat: -0.2, offsetLng: 1.5 },
            "Красноармейск": { offsetLat: -0.1, offsetLng: 1.7 },
            "Родионовская": { offsetLat: -0.1, offsetLng: 1.5 },
            "Тихорецк": { offsetLat: -0.1, offsetLng: 1 },
            "1235,3 км": { offsetLat: -0.07, offsetLng: 1.1 }
        };

        const labelCloseOffsets_1920 = {
            "ПСП 45 км": { offsetLat: 0.08, offsetLng: -0.07 },
            "КПОУ Жана Жол": { offsetLat: -0.1, offsetLng: -0.1 },
            "НПС им. Шманова": { offsetLat: -0.1, offsetLng: 0.2 },
            "НПС им. Касымова": { offsetLat: 0, offsetLng: -0.9 },
            "Новороссийск": { offsetLat: 0.1, offsetLng: -0.4 },
            "Грушовая": { offsetLat: -0.15, offsetLng: 0.1 },
            "Унеча": { offsetLat: -0.1, offsetLng: -0.2 },
            "Никольское": { offsetLat: 0.15, offsetLng: 0 },
            "Алашанькоу": { offsetLat: 0, offsetLng: 0.4 },
            "ГНПС Атасу": { offsetLat: 0, offsetLng: 0.4 },
            "ПНХЗ": { offsetLat: 0.15, offsetLng: 0 },
            "ГНПС Кумколь": { offsetLat: -0.1, offsetLng: -0.1 },
            "ГНПС Кенкияк": { offsetLat: 0.05, offsetLng: 0.35 },
            "ПКОП": { offsetLat: -0.1, offsetLng: -0.1 },
            "ПСП Самара": { offsetLat: 0, offsetLng: 0.25 },
            "Усть-Луга": { offsetLat: -0.05, offsetLng: 1.1 },
            "Большая Черниговка": { offsetLat: 0.1, offsetLng: 0.25 },
            "ГНПС им. Б. Джумагалиева": { offsetLat: 0.1, offsetLng: 0.35 },
            "Клин": { offsetLat: 0.15, offsetLng: 0 },
            "915 км н/пр.КЛ": { offsetLat: -0.05, offsetLng: 0.35 },
            "Красноармейск": { offsetLat: 0, offsetLng: 0.35 },
            "Родионовская": { offsetLat: 0, offsetLng: 0.35 },
            "Тихорецк": { offsetLat: 0, offsetLng: 0.2 },
            "1235,3 км": { offsetLat: 0.1, offsetLng: 0.2 }
        };

        // Смещения для 2560x1600
        const labelOffsets_2560 = {
            "ПСП 45 км": { offsetLat: 0.15, offsetLng: 0 },
            "КПОУ Жана Жол": { offsetLat: -0.35, offsetLng: 0 },
            "НПС им. Шманова": { offsetLat: -0.25, offsetLng: 1.8 },
            "НПС им. Касымова": { offsetLat: -0.1, offsetLng: -2.1 },
            "Новороссийск": { offsetLat: 0.1, offsetLng: -1.4 },
            "Грушовая": { offsetLat: -0.15, offsetLng: 1.15 },
            "Унеча": { offsetLat: -0.25, offsetLng: 0 },
            "Никольское": { offsetLat: 0.15, offsetLng: 0 },
            "Алашанькоу": { offsetLat: -0.1, offsetLng: 1.4 },
            "ГНПС Атасу": { offsetLat: -0.1, offsetLng: 1.4 },
            "ПНХЗ": { offsetLat: 0.15, offsetLng: 0 },
            "ГНПС Кумколь": { offsetLat: -0.35, offsetLng: 0 },
            "ГНПС Кенкияк": { offsetLat: -0.1, offsetLng: 1.6 },
            "ПКОП": { offsetLat: -0.4, offsetLng: 0 },
            "ПСП Самара": { offsetLat: 0, offsetLng: 1.3 },
            "Усть-Луга": { offsetLat: -0.05, offsetLng: 1.1 },
            "Большая Черниговка": { offsetLat: 0.1, offsetLng: 1.15 },
            "ГНПС им. Б. Джумагалиева": { offsetLat: 0.2, offsetLng: 1.5 },
            "Клин": { offsetLat: 0.15, offsetLng: 0 },
            "915 км н/пр.КЛ": { offsetLat: -0.2, offsetLng: 1.5 },
            "Красноармейск": { offsetLat: -0.1, offsetLng: 1.7 },
            "Родионовская": { offsetLat: -0.1, offsetLng: 1.5 },
            "Тихорецк": { offsetLat: -0.1, offsetLng: 1 },
            "1235,3 км": { offsetLat: -0.07, offsetLng: 1.1 }
        };

        const labelCloseOffsets_2560 = {
            "ПСП 45 км": { offsetLat: 0.08, offsetLng: -0.07 },
            "КПОУ Жана Жол": { offsetLat: -0.1, offsetLng: -0.1 },
            "НПС им. Шманова": { offsetLat: -0.1, offsetLng: 0.2 },
            "НПС им. Касымова": { offsetLat: 0, offsetLng: -0.9 },
            "Новороссийск": { offsetLat: 0.1, offsetLng: -0.4 },
            "Грушовая": { offsetLat: -0.15, offsetLng: 0.1 },
            "Унеча": { offsetLat: -0.1, offsetLng: -0.2 },
            "Никольское": { offsetLat: 0.15, offsetLng: 0 },
            "Алашанькоу": { offsetLat: 0, offsetLng: 0.4 },
            "ГНПС Атасу": { offsetLat: 0, offsetLng: 0.4 },
            "ПНХЗ": { offsetLat: 0.15, offsetLng: 0 },
            "ГНПС Кумколь": { offsetLat: -0.1, offsetLng: -0.1 },
            "ГНПС Кенкияк": { offsetLat: 0.05, offsetLng: 0.35 },
            "ПКОП": { offsetLat: -0.1, offsetLng: -0.1 },
            "ПСП Самара": { offsetLat: 0, offsetLng: 0.25 },
            "Усть-Луга": { offsetLat: -0.05, offsetLng: 1.1 },
            "Большая Черниговка": { offsetLat: 0.1, offsetLng: 0.25 },
            "ГНПС им. Б. Джумагалиева": { offsetLat: 0.1, offsetLng: 0.35 },
            "Клин": { offsetLat: 0.15, offsetLng: 0 },
            "915 км н/пр.КЛ": { offsetLat: -0.05, offsetLng: 0.35 },
            "Красноармейск": { offsetLat: 0, offsetLng: 0.35 },
            "Родионовская": { offsetLat: 0, offsetLng: 0.35 },
            "Тихорецк": { offsetLat: 0, offsetLng: 0.2 },
            "1235,3 км": { offsetLat: 0.1, offsetLng: 0.2 }
        };

        // Выбор данных в зависимости от разрешения
        if (screenWidth >= 2560 && screenHeight >= 1600) {
            labelOffsets = labelOffsets_2560;
            labelCloseOffsets = labelCloseOffsets_2560;
        } else {
            labelOffsets = labelOffsets_1920;
            labelCloseOffsets = labelCloseOffsets_1920;
        }

        points.forEach(point => {
            if (point.lat && point.lng) {
                // Основной маркер
                const marker = L.circleMarker([point.lat, point.lng], {
                    pane: 'pointsPane',
                    radius: 6,
                    color: 'black',
                    weight: 2,
                    fillColor: point.color,
                    fillOpacity: 1
                }).addTo(map);

                // Получаем смещения для дальнего названия
                const offsetFar = labelOffsets[point.name] || { offsetLat: 0, offsetLng: 0 };
                const latFar = point.lat + offsetFar.offsetLat;
                const lngFar = point.lng + offsetFar.offsetLng;

                // Получаем смещения для ближнего названия
                const offsetClose = labelCloseOffsets[point.name] || { offsetLat: 0, offsetLng: 0 };
                const latClose = point.lat + offsetClose.offsetLat;
                const lngClose = point.lng + offsetClose.offsetLng;

                // Смещенное название (дальняя метка)
                const labelFar = L.marker([latFar, lngFar], {
                    pane: 'pointsPane',
                    icon: L.divIcon({
                        className: 'marker-label far-label',
                        html: `<div>${point.name}</div>`,
                        iconSize: null,
                        iconAnchor: [60, 15]
                    }),
                }).addTo(map);

                // Ближнее название (при увеличенном масштабе, изначально скрыто)
                const labelClose = L.marker([latClose, lngClose], {
                    pane: 'pointsPane',
                    icon: L.divIcon({
                        className: 'marker-label close-label',
                        html: `<div>${point.name}</div>`,
                        iconSize: null,
                        iconAnchor: [40, 10]
                    })
                }).addTo(map);
                labelClose.getElement().style.display = 'none'; // Скрываем ближнее название по умолчанию

                markers.push({ name: point.name, marker, labelFar, labelClose });
            } else {
                console.warn(`Пропущена точка с ID ${point.id} из-за отсутствия координат.`);
            }
        });

        // Функция для обновления названий при изменении масштаба
        function updateLabelsOnZoom() {
            const currentZoom = map.getZoom();
            markers.forEach(markerObj => {
                if (currentZoom >= zoomThreshold) {
                    // Показываем ближнее название и скрываем дальнее
                    markerObj.labelFar.getElement().style.display = 'none';
                    markerObj.labelClose.getElement().style.display = 'block';
                } else {
                    // Показываем дальнее название и скрываем ближнее
                    markerObj.labelFar.getElement().style.display = 'block';
                    markerObj.labelClose.getElement().style.display = 'none';
                }
            });
        }

        // Обновляем названия при изменении масштаба
        map.on('zoomend', updateLabelsOnZoom);

        // Запускаем логику после загрузки
        updateLabelsOnZoom();
    })
    .catch(error => console.error('Ошибка загрузки данных:', error));


//--------------------------Резервуары----------------------------

// Настройка смещения для каждого резервуара
const reservoirOffsets = {
    1: { start: { lat: 0.15, lng: -0.07 }, end: { lat: 0.15, lng: 0.07 } },
    2: { start: { lat: 0.15, lng: -0.07 }, end: { lat: 0.15, lng: 0.07 } },
    3: { start: { lat: 0.15, lng: -0.07 }, end: { lat: 0.15, lng: 0.07 } },
    4: { start: { lat: 0.15, lng: -0.07 }, end: { lat: 0.15, lng: 0.07 } },
    5: { start: { lat: 0.1, lng: -0.1 }, end: { lat: 0.1, lng: 0.1 } }, // Для технических резервуаров
    6: { start: { lat: 0.1, lng: -0.1 }, end: { lat: 0.1, lng: 0.1 } }, // Для технических резервуаров
};

// Функция для расчёта процента заполненности
function getFillPercentage(volume, maxCapacity) {
    return Math.min(100, (volume / maxCapacity) * 100); // Не больше 100%
}

// Функция создания иконки с черным/белым заполнением для двух типов резервуаров
function createReservoirIcon(fillPercentage, width, height, type) {
    // Цвет рамки зависит от типа резервуара
    const borderColor = type === 0 ? "rgba(192, 38, 38, 0.99)" : "brown";

    return L.divIcon({
        html: `<div style="position: relative; width: ${width}px; height: ${height}px; border: 2px solid ${borderColor}; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);">
                    <div style="position: absolute; width: 100%; height: 100%; background: white;"></div>
                    <div style="position: absolute; width: 100%; height: ${fillPercentage}%; background: black; bottom: 0;"></div>
               </div>`,
        iconSize: [width, height],
        className: '',
    });
}


// Создаём слои для резервуаров
const pointTanksLayer = L.layerGroup(); // Точечные резервуары
const technicalTanksLayer = L.layerGroup(); // Технические резервуары

// Получаем данные резервуаров и их объемов из базы
fetch('database/getData.php?table=Reservoirs')
    .then(response => response.json())
    .then(reservoirs => {
        fetch('database/getData.php?table=reservoirvolumes')
            .then(response => response.json())
            .then(volumes => {
                const latestVolumes = {};
                volumes.forEach(volume => {
                    latestVolumes[volume.reservoir_id] = {
                        start_volume: volume.start_volume,
                        end_volume: volume.end_volume
                    };
                });

                reservoirs.forEach(reservoir => {
                    const volumeData = latestVolumes[reservoir.id] || { start_volume: 0, end_volume: 0 };
                    const coordStart = [reservoir.coords_start_latitude, reservoir.coords_start_longitude];
                    const coordEnd = [reservoir.coords_end_latitude, reservoir.coords_end_longitude];

                    const offset = reservoirOffsets[reservoir.id] || { start: { lat: 0.05, lng: 0 }, end: { lat: 0.05, lng: 0 } };
                    const coordStartLabel = [coordStart[0] + offset.start.lat, coordStart[1] + offset.start.lng];
                    const coordEndLabel = [coordEnd[0] + offset.end.lat, coordEnd[1] + offset.end.lng];


                    //--------------------------------------------------------Тут проработать колчиество по резервуарам!!!!
                    let maxCapacity = 10000; // По умолчанию 10 тыс. тонн
                    if (reservoir.name.includes("Кенкияк-Шманова")) {
                        maxCapacity = 5000;
                    } else if (reservoir.name.includes("Кенкияк-Кумоль")) {
                        maxCapacity = 15000;
                    }

                    const startFillPercentage = getFillPercentage(volumeData.start_volume, maxCapacity);
                    const endFillPercentage = getFillPercentage(volumeData.end_volume, maxCapacity);

                    if (reservoir.type === 0) {
                        // Точечные резервуары (красная рамка)
                        L.marker(coordStart, { icon: createReservoirIcon(startFillPercentage, 25, 40, reservoir.type) })
                            .bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${volumeData.start_volume} / ${maxCapacity} м³`)
                            .addTo(pointTanksLayer);
                    
                        L.marker(coordEnd, { icon: createReservoirIcon(endFillPercentage, 25, 40, reservoir.type) })
                            .bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${volumeData.end_volume} / ${maxCapacity} м³`)
                            .addTo(pointTanksLayer);
                    } else if (reservoir.type === 1) {
                        // Технические резервуары (коричневая рамка)
                        L.marker(coordStart, { icon: createReservoirIcon(startFillPercentage, 35, 25, reservoir.type) })
                            .bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${volumeData.start_volume} / ${maxCapacity} м³`)
                            .addTo(technicalTanksLayer);
                    
                        L.marker(coordEnd, { icon: createReservoirIcon(endFillPercentage, 35, 25, reservoir.type) })
                            .bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${volumeData.end_volume} / ${maxCapacity} м³`)
                            .addTo(technicalTanksLayer);
                    }
                    

                    // Линии между точками (для всех типов)
                    L.polyline([coordStart, coordEnd], {
                        color: '#722600',
                        weight: 4,
                        opacity: 0.7
                    }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

                    // Линии от маркеров к меткам с объемами
                    L.polyline([coordStart, coordStartLabel], {
                        color: 'black',
                        weight: 2,
                        opacity: 0.8,
                        dashArray: '4,2'
                    }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

                    L.polyline([coordEnd, coordEndLabel], {
                        color: 'black',
                        weight: 2,
                        opacity: 0.8,
                        dashArray: '4,2'
                    }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

                    // Метки с объемами рядом с резервуарами
                    L.marker(coordStartLabel, {
                        icon: L.divIcon({
                            html: `<div style="white-space: nowrap; padding: 6x 10x; font-weight: bold; transform: translateY(-10px);">
                                ${volumeData.start_volume} м³
                            </div>`,
                            className: ''
                        })
                    }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

                    L.marker(coordEndLabel, {
                        icon: L.divIcon({
                            html: `<div style="white-space: nowrap; padding: 6x 10x; font-weight: bold; transform: translateY(-10px);">
                                ${volumeData.end_volume} м³
                            </div>`,
                            className: ''
                        })
                    }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);
                });

                updateLayerVisibility();
            })
            .catch(error => console.error('Ошибка загрузки данных объемов нефти:', error));
    })
    .catch(error => console.error('Ошибка загрузки данных резервуаров:', error));

// Управление видимостью слоев
const minZoom = 7.5;

function updateLayerVisibility() {
    const currentZoom = map.getZoom();

    if (currentZoom >= minZoom) {
        if (!map.hasLayer(pointTanksLayer)) map.addLayer(pointTanksLayer);
        if (!map.hasLayer(technicalTanksLayer)) map.addLayer(technicalTanksLayer);
    } else {
        if (map.hasLayer(pointTanksLayer)) map.removeLayer(pointTanksLayer);
        if (map.hasLayer(technicalTanksLayer)) map.removeLayer(technicalTanksLayer);
    }
}

// Привязываем обновление видимости слоев к событию изменения зума
map.on('zoomend', updateLayerVisibility);

// Проверяем начальное состояние видимости
map.on('load', updateLayerVisibility);

// Вызываем обновление видимости сразу после инициализации
updateLayerVisibility();







//--------------------------Линии между точками-------------------------------------

map.createPane('linesPane');
map.getPane('linesPane').style.zIndex = 400; // Низкий zIndex для линий

// Объект цветов компаний
const companyColors = {
    "АО КазТрансОйл": "rgb(3, 198, 252)",
    "ПАО Транснефть": "rgb(79, 73, 239)",
    "ТОО «Казахстанско-Китайский трубопровод»": "rgb(5, 186, 53)",
    "АО 'СЗТК' МунайТас'": "rgb(221, 5, 221)"
};


// Основная логика для добавления линий на карту
async function main() {
    const pipelinesWithIds = await fetchPipelinesFromDB();
    const points = await fetchPointsFromDB();

    if (pipelinesWithIds.length === 0 || points.length === 0) {
        console.error("Недостаточно данных для отрисовки линий.");
        return;
    }

    pipelinesWithIds.forEach(({ from, to, company }) => {
        const point1 = points.find(p => p.id === from);
        const point2 = points.find(p => p.id === to);

        if (!point1 || !point2) {
            console.warn(`Не найдены точки: from=${from}, to=${to}`);
            return;
        }

        if (!point1.coords || !point2.coords) {
            console.warn(`Некорректные координаты для точек: from=${from}, to=${to}`);
            return;
        }

        const mainLineColor = companyColors[company] || "black";

        // Основная линия
        const mainLine = L.polyline([point1.coords, point2.coords], {
            pane: 'linesPane', 
            color: mainLineColor,
            weight: 6,
            opacity: 0.8,
        }).addTo(map);

        // Пунктирная линия
        const dashedLine = L.polyline([point1.coords, point2.coords], {
            pane: 'linesPane', 
            color: "black",
            weight: 3,
            dashArray: "10, 10",
            opacity: 1,
            className: "dashed-line",
        }).addTo(map);

        // Добавление стрелок, если они нужны
        const noArrowLines = [
            { from: 12, to: 5 },
            { from: 11, to: 5 },
            { from: 7, to: 19 },
            { from: 24, to: 13 },
            { from: 15, to: 9 }
        ];

        const hasArrow = !noArrowLines.some(line => line.from === from && line.to === to);

        if (hasArrow) {
            const arrowDecorator = L.polylineDecorator(mainLine, {
                patterns: [
                    {
                        offset: '50%',
                        repeat: 0,
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,
                            pathOptions: { color: mainLineColor, fillOpacity: 1 }
                        })
                    }
                ]
            }).addTo(map);
        }
    });
}

// Вызов основной функции
main();







//----------------------------------Информация хода нефти с отображением стрелок-------------------------------


// Создаем слой для отображения линий и меток
const flowLayerGroup = L.layerGroup().addTo(map);

// Настройки направлений для точек (смещения по широте и долготе)
const directionOffsets = {
    1: { lat: 0.5, lng: 0.3 },   // Алашанькоу
    2: { lat: 0.2, lng: 0.5 },    // Атасу
    3: { lat: 0.5, lng: 0.3 },    // ПНХЗ
    4: { lat: 0.5, lng: 0.3},   // Кумколь
    5: { lat: 0.2, lng: 0.5 },   // Кенкияк
    6: { lat: 0.2, lng: 0.3 },   // ПКОП
    7: { lat: 0.4, lng: 0.2 },   // Шманова
    8: { lat: 0.2, lng: 0.2 },   // Самара
    9: { lat: -0.5, lng: -0.5 },   // Новороссийск
    10: { lat: 0.2, lng: 0.3 },  // Усть-Луга
    19: { lat: -0.3, lng: -0.2 },  // Касымова
    24: { lat: -0.3, lng: 0.6 },  // 1235
};

// Обновленная функция поиска свободного места
function findFreePosition(coords, layerGroup, pointId) {
    const baseOffset = 0.5; // Базовое смещение
    const defaultDirections = [
        [baseOffset, baseOffset],   // Верхний правый угол
        [baseOffset, -baseOffset],  // Верхний левый угол
        [-baseOffset, baseOffset],  // Нижний правый угол
        [-baseOffset, -baseOffset], // Нижний левый угол
    ];

    const customOffset = directionOffsets[pointId] || { lat: 0, lng: 0 };
    const directions = [
        [customOffset.lat, customOffset.lng], // Индивидуальное смещение для точки
        ...defaultDirections                  // Остальные стандартные направления
    ];

    for (let i = 0; i < directions.length; i++) {
        const candidateCoords = [
            coords[0] + directions[i][0],
            coords[1] + directions[i][1]
        ];

        // Проверяем, пересекается ли с существующими элементами
        const isOverlapping = Array.from(layerGroup.getLayers()).some(layer => {
            if (layer.getLatLng) {
                const layerCoords = layer.getLatLng();
                return (
                    Math.abs(layerCoords.lat - candidateCoords[0]) < baseOffset / 2 &&
                    Math.abs(layerCoords.lng - candidateCoords[1]) < baseOffset / 2
                );
            }
            if (layer instanceof L.Polyline) {
                // Проверяем пересечение с линиями
                const latlngs = layer.getLatLngs();
                return latlngs.some(latlng =>
                    Math.abs(latlng.lat - candidateCoords[0]) < baseOffset / 2 &&
                    Math.abs(latlng.lng - candidateCoords[1]) < baseOffset / 2
                );
            }
            return false;
        });

        if (!isOverlapping) {
            return candidateCoords; // Возвращаем первое свободное место
        }
    }

    // Если нет свободного места, возвращаем стандартное смещение
    return [coords[0] + baseOffset, coords[1] + baseOffset];
}

// Обновляем вызов addMinimalistFlow
function addMinimalistFlow(points, oilTransferData) {
    flowLayerGroup.clearLayers(); // Очищаем слой перед добавлением новых элементов

    const uniqueEntries = new Set(); // Для фильтрации дублирующих данных

    oilTransferData.forEach(record => {
        const toPoint = points.find(point => point.id === record.to_point); // Находим конечную точку

        if (toPoint && toPoint.coords) {
            const recordKey = `${record.to_point}-${record.to_amount}-${record.source_type || 'pipeline'}`; // Уникальный ключ с учетом источника

            if (!uniqueEntries.has(recordKey)) {
                uniqueEntries.add(recordKey); // Добавляем запись в множество

                const labelPosition = findFreePosition(toPoint.coords, flowLayerGroup, record.to_point);

                const markerHtml = `
                    <div>
                        ${record.to_amount} тн 
                    </div>
                `;

                // Линия от точки к метке
                L.polyline([toPoint.coords, labelPosition], {
                    color: 'black',
                    weight: 2,
                    dashArray: '5, 5',
                    opacity: 0.8,
                }).addTo(flowLayerGroup);

                // Метка с количеством нефти и источником
                L.marker(labelPosition, {
                    icon: L.divIcon({
                        className: 'flow-label',
                        html: markerHtml,
                        iconSize: null,
                        iconAnchor: [4, 18],
                    }),
                }).addTo(flowLayerGroup);
            }
        }
    });
}

// Стили для меток
const style = document.createElement('style');
style.innerHTML = `
.flow-label div {
    font-size: 14px;
    font-weight: bold;
    color: black;
    border-radius: 5px; 
    padding: 5px;
    text-align: center;
    white-space: nowrap;
}
.flow-label span {
    font-weight: normal;
}
`;
document.head.appendChild(style);

// Инициализация карты
async function initializeMinimalistFlowMap() {
    const points = await fetchPointsFromDB();
    const oilTransferData = await fetchOilTransferFromDB();

    console.log('Точки:', points);
    console.log('Данные о нефти:', oilTransferData);

if (points.length === 0 || oilTransferData.length === 0) {
        console.error('Недостаточно данных для отрисовки карты.');
        return;
    }

    addMinimalistFlow(points, oilTransferData);
}

initializeMinimalistFlowMap();



//------------------------------Отображение нефти на трубопроводе-------------------
// Функция отображения только количества нефти, исходящей из точки
async function addOutgoingOilAmounts(points, oilTransferData) {
    const flowLayerGroup = L.layerGroup(); // Создаем слой для потоков

    const staticLabelPositions = {
        '4-2': [47.5, 69], // От точки 4 к точке 2
        '4-3': [49.7, 72.4], // От точки 4 к точке 3
        '4-6': [45.15, 68.65], // От точки 4 к точке 6
        '5-7': [48.5, 56], // От точки 5 к точке 7
        '5-4': [48.5, 57.908], // От точки 5 к точке 4
        '8-9': [52.22, 48], // От точки 8 к точке 9
        '8-10': [53.2, 49], // От точки 8 к точке 10
    };

    const lineColors = {
        '4-2': 'rgb(3, 198, 252)',
        '4-3': 'rgb(3, 198, 252)',
        '4-6': 'rgb(3, 198, 252)',
        '5-7': 'rgb(221, 5, 221)',
        '5-4': 'rgb(5, 186, 53)',
        '8-9': 'rgb(79, 73, 239)',
        '8-10': 'rgb(79, 73, 239)',
    };
    
    const multiOutputPoints = [4, 5, 8]; // Точки с несколькими исходящими потоками

    multiOutputPoints.forEach(pointId => {
        // Получаем исходящие записи для точки
        const outgoingTransfers = oilTransferData.filter(record => record.from_point === pointId);

        if (outgoingTransfers.length > 0) {
            const fromPoint = points.find(point => point.id === pointId);

            if (!fromPoint || !fromPoint.coords) {
                console.warn(`Точка с ID ${pointId} не найдена или не имеет координат.`);
                return;
            }

            outgoingTransfers.forEach(transfer => {
                const toPoint = points.find(point => point.id === transfer.to_point);

                if (!toPoint || !toPoint.coords) {
                    console.warn(`Конечная точка с ID ${transfer.to_point} не найдена или не имеет координат.`);
                    return;
                }

                // Используем статичные позиции, если они заданы
                const staticKey = `${transfer.from_point}-${transfer.to_point}`;
                const labelPosition = staticLabelPositions[staticKey] || [
                    (fromPoint.coords[0] + toPoint.coords[0]) / 2,
                    (fromPoint.coords[1] + toPoint.coords[1]) / 2,
                ];

                const labelColor = lineColors[staticKey] || 'black';

                // Добавляем только метку с количеством нефти
                L.marker(labelPosition, {
                    icon: L.divIcon({
                        className: 'flow-label',
                        html: `<div style="color: ${labelColor}; text-shadow: -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black, 1px 1px 0 black;">${transfer.to_amount} тн</div>`,
                        iconSize: null,
                    }),
                }).addTo(flowLayerGroup);
            });
        }
    });

    flowLayerGroup.addTo(map); // Добавляем слой на карту
}

// Вызов функции
(async function initializeOutgoingOilAmounts() {
    const points = await fetchPointsFromDB();
    const oilTransferData = await fetchOilTransferFromDB();

    if (points.length > 0 && oilTransferData.length > 0) {
        addOutgoingOilAmounts(points, oilTransferData);
    } else {
        console.error('Недостаточно данных для отображения исходящих объемов нефти.');
    }
})();




//---------------------------Таблица с информацией---------------------------

// Функция обновления таблицы
function updateTable(data, pointId) {
    const tableContainer = document.getElementById('info-table-container');
    const tableBody = document.getElementById('info-table').querySelector('tbody');

    // Очищаем таблицу
    tableBody.innerHTML = '';

    if (!data || data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6">Нет данных для отображения</td></tr>';
    } else {
        data.forEach(row => {
            addTableRow(row, tableBody, pointId);
        });
    }

    // Показываем таблицу
    tableContainer.style.display = 'block';
}

// Функция для добавления строки в таблицу
function addTableRow(row, tableBody = null, pointId) {
    if (!tableBody) {
        tableBody = document.getElementById('info-table').querySelector('tbody');
    }

    const tr = document.createElement('tr');
    tr.dataset.id = row.id; // Сохранение ID записи

    tr.innerHTML = `
        <td contenteditable="true" data-field="date">${row.date || 'Не указано'}</td>
        <td contenteditable="true" data-field="from_name">${row.from_name || 'Не указано'}</td>
        <td contenteditable="true" data-field="to_name">${row.to_name || 'Не указано'}</td>
        <td contenteditable="true" data-field="amount">${row.amount || 0}</td>
        <td contenteditable="true" data-field="losses">${row.losses || 0}</td>
        <td>
            <button class="save-btn">Сохранить</button>
            <button class="delete-btn">Удалить</button>
        </td>
    `;

    // Добавление обработчиков событий
    tr.querySelector('.save-btn').addEventListener('click', () => saveRow(tr, pointId));
    tr.querySelector('.delete-btn').addEventListener('click', () => deleteRow(tr));

    tableBody.appendChild(tr);
}

// Функция сохранения изменений
function saveRow(row, pointId) {
    const id = row.dataset.id;
    if (!id) {
        alert('Ошибка: нет ID записи!');
        return;
    }

    const updatedData = {
        id: id,
        pointId: pointId,
        date: row.querySelector('[data-field="date"]').innerText,
        from_name: row.querySelector('[data-field="from_name"]').innerText,
        to_name: row.querySelector('[data-field="to_name"]').innerText,
        amount: row.querySelector('[data-field="amount"]').innerText,
        losses: row.querySelector('[data-field="losses"]').innerText,
    };

    fetch('database/updateData.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Данные обновлены');
        } else {
            alert('Ошибка при обновлении данных');
        }
    })
    .catch(error => console.error('Ошибка сохранения:', error));
}

// Функция удаления строки
function deleteRow(row) {
    const id = row.dataset.id;
    if (!id) {
        alert('Ошибка: нет ID записи!');
        return;
    }

    if (!confirm('Вы уверены, что хотите удалить эту запись?')) return;

    fetch('database/deleteData.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            row.remove();
            alert('Запись удалена');
        } else {
            alert('Ошибка при удалении');
        }
    })
    .catch(error => console.error('Ошибка удаления:', error));
}

function addNewRow(pointId) {
    const newData = {
        pointId: pointId,
        pipeline_id: 1, // Здесь укажите существующий ID из таблицы pipelines
        date: 'Новая дата',
        from_name: 'Источник',
        to_name: 'Получатель',
        amount: 0,
        losses: 0
    };

    console.log('Данные для отправки:', newData);

    fetch('database/addData.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Ответ от сервера:', data);
        if (data.success) {
            newData.id = data.id; // Устанавливаем ID новой записи
            addTableRow(newData, null, pointId);
            alert('Запись добавлена');
        } else {
            alert('Ошибка при добавлении записи: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => console.error('Ошибка добавления:', error));
}

let currentPointId = null; // Глобальная переменная для текущего ID точки

// Пример добавления маркеров
fetch('database/getData.php?table=Points')
    .then(response => response.json())
    .then(points => {
        points.forEach(point => {
            if (point.lat && point.lng) {
                const marker = L.circleMarker([point.lat, point.lng], {
                    pane: 'pointsPane',
                    radius: 6,
                    color: 'black',
                    weight: 2,
                    fillColor: point.color,
                    fillOpacity: 1,
                }).addTo(map);

                // При клике на маркер сохраняем текущий ID точки
                marker.on('click', () => {
                    currentPointId = point.id; // Устанавливаем ID точки
                    console.log('Выбрана точка с ID:', currentPointId);

                    fetch(`database/TableInfo.php?pointId=${point.id}`)
                        .then(response => response.json())
                        .then(data => {
                            updateTable(data, point.id);
                        })
                        .catch(error => console.error('Ошибка загрузки данных о точке:', error));
                });
            }
        });
    })
    .catch(error => console.error('Ошибка загрузки данных:', error));

// Обработчик для кнопки "Добавить запись"
document.getElementById('add-row-btn').addEventListener('click', () => {
    console.log('Кнопка "Добавить запись" нажата');
    if (currentPointId) {
        addNewRow(currentPointId); // Передаём текущий ID точки
    } else {
        alert('Ошибка: не выбрана точка на карте');
    }
});




// //--------------------------------Потери------------------------

// // Пример данных с потерями для каждого трубопровода
// // Эти данные будут взяты из базы данных в будущем
// const pipelineLosses = {
//     "7-19": 100, // Потери между НПС им. Шманова и НПС им. Касымова
//     "5-7": 50,  // Потери между ПСП Самара и Клин
//     "5-4": 75, // Потери между Клин и Никольское
//     "14-2": 60, // Потери между Никольское и Унеча
//     // Добавьте данные для других трубопроводов
// };

// const minZoomToShowLossCircles = 7.5; // Минимальный зум для отображения потерь

// // Создаем слой для потерь
// const lossCirclesLayer = L.layerGroup();

// pipelinesWithIds.forEach(({ from, to }, index) => {
//     const point1 = points.find(p => p.id === from);
//     const point2 = points.find(p => p.id === to);

//     if (!point1 || !point2) {
//         console.warn(`Не найдены точки для связи: ${from}-${to}`);
//         return; // Пропускаем эту связь
//     }

//     const lineKey = `${from}-${to}`;
//     const loss = pipelineLosses[lineKey];

//     if (loss !== undefined) {
//         // Позиция начала линии (середина трубопровода)
//         const midLat = (point1.coords[0] + point2.coords[0]) / 2;
//         const midLon = (point1.coords[1] + point2.coords[1]) / 2;

//         // Вектор направления между точками
//         const dx = point2.coords[0] - point1.coords[0];
//         const dy = point2.coords[1] - point1.coords[1];
//         const lengthOffset = 0.1; // Фиксированная длина линии

//         // Нормализация вектора
//         const magnitude = Math.sqrt(dx * dx + dy * dy);
//         const normalizedDx = dx / magnitude;
//         const normalizedDy = dy / magnitude;

//         // Смещение линии на фиксированную длину
//         const offsetLat = midLat + normalizedDy * lengthOffset;
//         const offsetLon = midLon - normalizedDx * lengthOffset;

//         // Линия от трубопровода
//         const lossLine = L.polyline(
//             [[midLat, midLon], [offsetLat, offsetLon]],
//             { color: 'red', weight: 2, dashArray: '5' } // Стиль линии
//         ).addTo(lossCirclesLayer);

//         // Текстовая метка рядом с концом линии
//         const lossLabel = L.divIcon({
//             className: 'loss-label',
//             html: ` 
//                 <div style="
//                     color: red;
//                     font-size: 12px;
//                     font-weight: bold;
//                     white-space: nowrap;
//                 ">
//                     ${loss}тн потери
//                 </div>
//             `,
//             iconSize: [50, 20], // Размеры метки
//             iconAnchor: [-5, 5] // Центр метки относительно точки
//         });

//         // Добавляем текстовую метку
//         L.marker([offsetLat, offsetLon], { icon: lossLabel }).addTo(lossCirclesLayer);
//     } else {
//         console.warn(`Нет данных о потерях для трубопровода ${lineKey}`);
//     }
// });






// // Логика отображения/скрытия потерь при изменении зума
// map.on('zoomend', () => {
//     const currentZoom = map.getZoom();

//     if (currentZoom >= minZoomToShowLossCircles) {
//         if (!map.hasLayer(lossCirclesLayer)) {
//             map.addLayer(lossCirclesLayer); // Показываем потери
//         }
//     } else {
//         if (map.hasLayer(lossCirclesLayer)) {
//             map.removeLayer(lossCirclesLayer); // Скрываем потери
//         }
//     }
// });



// //--------------------------------------------------------




const filterButton = document.getElementById('checkboxOne');
let flowLayerVisible = true;

filterButton.addEventListener('click', () => {
    flowLayerVisible = !flowLayerVisible;

    // Управляем видимостью слоя
    if (flowLayerVisible) {
        map.addLayer(flowLayerGroup); // Показываем слой
        
    } else {
        map.removeLayer(flowLayerGroup); // Скрываем слой
        
    }
});

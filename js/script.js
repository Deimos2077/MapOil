        // Инициализация карты
        var map = L.map('map', {
            center: [51.5, 57], 
            zoom: 5,          
            minZoom: 5,       
            maxZoom: 10,      
            zoomSnap: 0.001,    
            zoomDelta: 0.001,   
            zoomControl: false 
            });

        // Установка ограничений карты
        var southWest = L.latLng(40, 27);  
        var northEast = L.latLng(60, 85); 
        var bounds = L.latLngBounds(southWest, northEast);
        
        map.setMaxBounds(bounds);
        
        // Установка минимального и максимального зума
        map.options.minZoom = 5;  
        map.options.maxZoom = 10; 
        
        map.on('drag', function () {
            map.panInsideBounds(bounds, { animate: false });
        });
        
        map.on('zoom', function () {
            if (map.getZoom() < map.options.minZoom) {
                map.setZoom(map.options.minZoom);
            }
        });


        // Подключение OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attributionControl: false
        }).addTo(map);
    
        // Подключение библиотеки Leaflet.easyPrint
        var printer = L.easyPrint({
            title: 'Распечатать карту',
            position: 'topleft', 
            sizeModes: ['A4Portrait', 'A4Landscape'],
            exportOnly: false, 
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
        color: 'red',  
        weight: 3,  
        opacity: 0.5,       
        fillOpacity: 0     
    };

// Загружаем и добавляем первый GeoJSON
    fetch('json/kz_0.json')
        .then(response => response.json())
        .then(data => {
        L.geoJSON(data, { style: geoJsonStyle }).addTo(map);
    });



//----------------------------------Подключение базы данных-------------------------------

// Создаем слой для резервуаров (Глобальная переменная)
const reservoirLayerGroup = L.layerGroup().addTo(map);
const outgoingFlowLayerGroup = L.layerGroup().addTo(map);


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
async function fetchOilTransferFromDB(year, month) {
    try {
        const response = await fetch(`database/getData.php?table=oiltransfer&year=${year}&month=${month}`);
        if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

        const data = await response.json();
        console.log("🔍 Ответ от сервера:", data);

        if (!Array.isArray(data)) {
            console.error("❌ ОШИБКА: `data` не массив!", data);
            return [];
        }

        let results = [];

        data.forEach(record => {
            let isSpecialSource = (record.from_point_id == 12 || record.from_point_id == 11); // ПСП 45 и Жана Жол

            // Кенкияк (id = 5) не отображается, но участвует в расчетах
            if (record.to_point_id === 5) {
                console.log(`📌 Кенкияк (id 5) получит нефть от ${record.from_point_id}: ${record.from_amount} тн`);
            }

            // Обычная запись (перемещение нефти)
            results.push({
                id: record.id,
                from_point: record.from_point_id,
                to_point: record.to_point_id,
                from_amount: record.from_amount,
                to_amount: record.to_amount,
                losses: record.losses || 0
            });

            // Специально добавляем ПСП 45 и Жана Жол в визуализацию
            if (isSpecialSource) {
                results.push({
                    id: `${record.id}-sent`,
                    from_point: record.from_point_id,
                    to_point: record.to_point_id, // Оставляем конечную точку, нефть должна визуализироваться
                    from_amount: record.from_amount,
                    to_amount: record.from_amount, // Дублируем, чтобы корректно отображалось
                    losses: 0
                });
            }
        });

        console.log("📊 Итоговый массив данных для визуализации:", results);
        return results;
    } catch (error) {
        console.error('❌ Ошибка загрузки данных о передаче нефти:', error);
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
            "ПСП 45 км": { offsetLat: 0.3, offsetLng: 0 },
            "КПОУ Жана Жол": { offsetLat: -0.5, offsetLng: 0 },
            "НПС им. Шманова": { offsetLat: -0.5, offsetLng: 1.8 },
            "НПС им. Касымова": { offsetLat: -0.1, offsetLng: -3.1 },
            "Новороссийск": { offsetLat: 0.1, offsetLng: -2.4 },
            "Грушовая": { offsetLat: -0.15, offsetLng: 2.15 },
            "Унеча": { offsetLat: -0.36, offsetLng: 0 },
            "Никольское": { offsetLat: 0.3, offsetLng: 0 },
            "Алашанькоу": { offsetLat: -0.1, offsetLng: 2.4 },
            "ГНПС Атасу": { offsetLat: -0.1, offsetLng: 2.4 },
            "ПНХЗ": { offsetLat: 0.3, offsetLng: 0 },
            "ГНПС Кумколь": { offsetLat: -0.5, offsetLng: 0 },
            "ГНПС Кенкияк": { offsetLat: -0.1, offsetLng: 2.3 },
            "ПКОП": { offsetLat: -0.6, offsetLng: 0 },
            "ПСП Самара": { offsetLat: 0, offsetLng: 1.8 },
            "Усть-Луга": { offsetLat: -0.05, offsetLng: 1.5 },
            "Большая Черниговка": { offsetLat: 0.1, offsetLng: 2 },
            "ГНПС им. Б. Джумагалиева": { offsetLat: 0.2, offsetLng: 2.5 },
            "Клин": { offsetLat: 0.5, offsetLng: 0 },
            "915 км н/пр.КЛ": { offsetLat: -0.2, offsetLng: 2.25 },
            "Красноармейск": { offsetLat: -0.50, offsetLng: 1.7 },
            "Родионовская": { offsetLat: -0.1, offsetLng: 2.5 },
            "Тихорецк": { offsetLat: -0.1, offsetLng: 1.5 },
            "1235,3 км": { offsetLat: -0.50, offsetLng: 1.5 },
            "Лопатино": { offsetLat: -0.5, offsetLng: 0 },
            "Калейкино": { offsetLat: -0.1, offsetLng: 1.5 },
            "Воротынец": { offsetLat: -0.05, offsetLng: 1.8 },
            "Горький": { offsetLat: 0.2, offsetLng: 1.3 },
            "Залесье": { offsetLat: -0.1, offsetLng: 1.2 },
            "Андреаполь": { offsetLat: 0.1, offsetLng: 2 },
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

function addReservoirs(reservoirs) {
    pointTanksLayer.clearLayers();
    technicalTanksLayer.clearLayers();

    reservoirs.forEach(reservoir => {
        const volumeData = {
            start_volume: reservoir.start_volume || 0,
            end_volume: reservoir.end_volume || 0
        };

        const coordStart = [reservoir.lat, reservoir.lng];
        const coordEnd = [reservoir.end_lat, reservoir.end_lng];

        const offset = reservoirOffsets[reservoir.id] || { start: { lat: 0.05, lng: 0 }, end: { lat: 0.05, lng: 0 } };
        const coordStartLabel = [coordStart[0] + offset.start.lat, coordStart[1] + offset.start.lng];
        const coordEndLabel = [coordEnd[0] + offset.end.lat, coordEnd[1] + offset.end.lng];

        let maxCapacity = 10000;
        if (reservoir.name.includes("Шманова")) maxCapacity = 5000;
        else if (reservoir.name.includes("Кумоль")) maxCapacity = 15000;

        const startFill = getFillPercentage(volumeData.start_volume, maxCapacity);
        const endFill = getFillPercentage(volumeData.end_volume, maxCapacity);

        const layer = reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer;

        // Маркеры
        L.marker(coordStart, {
            icon: createReservoirIcon(startFill, 25, 40, reservoir.type)
        }).bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${volumeData.start_volume} / ${maxCapacity} м³`)
          .addTo(layer);

        L.marker(coordEnd, {
            icon: createReservoirIcon(endFill, 25, 40, reservoir.type)
        }).bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${volumeData.end_volume} / ${maxCapacity} м³`)
          .addTo(layer);

        // Линии
        L.polyline([coordStart, coordEnd], {
            color: '#722600', weight: 4, opacity: 0.7
        }).addTo(layer);

        L.polyline([coordStart, coordStartLabel], {
            color: 'black', weight: 2, opacity: 0.8, dashArray: '4,2'
        }).addTo(layer);

        L.polyline([coordEnd, coordEndLabel], {
            color: 'black', weight: 2, opacity: 0.8, dashArray: '4,2'
        }).addTo(layer);

        // Подписи
        L.marker(coordStartLabel, {
            icon: L.divIcon({
                html: `<div style="white-space: nowrap; font-weight: bold;">${volumeData.start_volume} м³</div>`,
                className: ''
            })
        }).addTo(layer);

        L.marker(coordEndLabel, {
            icon: L.divIcon({
                html: `<div style="white-space: nowrap; font-weight: bold;">${volumeData.end_volume} м³</div>`,
                className: ''
            })
        }).addTo(layer);
    });

// добавляем только если включён чекбокс
const checkbox = document.getElementById('checkboxTwo');
if (checkbox && checkbox.checked) {
    map.addLayer(pointTanksLayer);
    map.addLayer(technicalTanksLayer);
}

}

document.getElementById('checkboxTwo').addEventListener('change', async function () {
    if (this.checked) {
        // Повторно отрисовываем резервуары при включении
        const [year, month] = document.getElementById('month-input').value.split('-');
        const reservoirs = await fetchReservoirVolumesFromDB(year, month);
        addReservoirs(reservoirs);
    } else {
        // Удаляем с карты
        map.removeLayer(pointTanksLayer);
        map.removeLayer(technicalTanksLayer);
    }
});






// // Получаем данные резервуаров и их объемов из базы
// fetch('database/getData.php?table=Reservoirs')
//     .then(response => response.json())
//     .then(reservoirs => {
//         fetch('database/getData.php?table=reservoirvolumes')
//             .then(response => response.json())
//             .then(volumes => {
//                 const latestVolumes = {};
//                 volumes.forEach(volume => {
//                     latestVolumes[volume.reservoir_id] = {
//                         start_volume: volume.start_volume,
//                         end_volume: volume.end_volume
//                     };
//                 });

//                 reservoirs.forEach(reservoir => {
//                     const volumeData = latestVolumes[reservoir.id] || { start_volume: 0, end_volume: 0 };
//                     const coordStart = [reservoir.coords_start_latitude, reservoir.coords_start_longitude];
//                     const coordEnd = [reservoir.coords_end_latitude, reservoir.coords_end_longitude];

//                     const offset = reservoirOffsets[reservoir.id] || { start: { lat: 0.05, lng: 0 }, end: { lat: 0.05, lng: 0 } };
//                     const coordStartLabel = [coordStart[0] + offset.start.lat, coordStart[1] + offset.start.lng];
//                     const coordEndLabel = [coordEnd[0] + offset.end.lat, coordEnd[1] + offset.end.lng];


//                     //--------------------------------------------------------Тут проработать колчиество по резервуарам!!!!
//                     let maxCapacity = 10000; // По умолчанию 10 тыс. тонн
//                     if (reservoir.name.includes("Кенкияк-Шманова")) {
//                         maxCapacity = 5000;
//                     } else if (reservoir.name.includes("Кенкияк-Кумоль")) {
//                         maxCapacity = 15000;
//                     }

//                     const startFillPercentage = getFillPercentage(volumeData.start_volume, maxCapacity);
//                     const endFillPercentage = getFillPercentage(volumeData.end_volume, maxCapacity);

//                     if (reservoir.type === 0) {
//                         // Точечные резервуары (красная рамка)
//                         L.marker(coordStart, { icon: createReservoirIcon(startFillPercentage, 25, 40, reservoir.type) })
//                             .bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${volumeData.start_volume} / ${maxCapacity} м³`)
//                             .addTo(pointTanksLayer);
                    
//                         L.marker(coordEnd, { icon: createReservoirIcon(endFillPercentage, 25, 40, reservoir.type) })
//                             .bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${volumeData.end_volume} / ${maxCapacity} м³`)
//                             .addTo(pointTanksLayer);
//                     } else if (reservoir.type === 1) {
//                         // Технические резервуары (коричневая рамка)
//                         L.marker(coordStart, { icon: createReservoirIcon(startFillPercentage, 35, 25, reservoir.type) })
//                             .bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${volumeData.start_volume} / ${maxCapacity} м³`)
//                             .addTo(technicalTanksLayer);
                    
//                         L.marker(coordEnd, { icon: createReservoirIcon(endFillPercentage, 35, 25, reservoir.type) })
//                             .bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${volumeData.end_volume} / ${maxCapacity} м³`)
//                             .addTo(technicalTanksLayer);
//                     }
                    

//                     // Линии между точками (для всех типов)
//                     L.polyline([coordStart, coordEnd], {
//                         color: '#722600',
//                         weight: 4,
//                         opacity: 0.7
//                     }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

//                     // Линии от маркеров к меткам с объемами
//                     L.polyline([coordStart, coordStartLabel], {
//                         color: 'black',
//                         weight: 2,
//                         opacity: 0.8,
//                         dashArray: '4,2'
//                     }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

//                     L.polyline([coordEnd, coordEndLabel], {
//                         color: 'black',
//                         weight: 2,
//                         opacity: 0.8,
//                         dashArray: '4,2'
//                     }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

//                     // Метки с объемами рядом с резервуарами
//                     L.marker(coordStartLabel, {
//                         icon: L.divIcon({
//                             html: `<div style="white-space: nowrap; padding: 6x 10x; font-weight: bold; transform: translateY(-10px);">
//                                 ${volumeData.start_volume} м³
//                             </div>`,
//                             className: ''
//                         })
//                     }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);

//                     L.marker(coordEndLabel, {
//                         icon: L.divIcon({
//                             html: `<div style="white-space: nowrap; padding: 6x 10x; font-weight: bold; transform: translateY(-10px);">
//                                 ${volumeData.end_volume} м³
//                             </div>`,
//                             className: ''
//                         })
//                     }).addTo(reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer);
//                 });

//                 updateLayerVisibility();
//             })
//             .catch(error => console.error('Ошибка загрузки данных объемов нефти:', error));
//     })
//     .catch(error => console.error('Ошибка загрузки данных резервуаров:', error));

// // Управление видимостью слоев
// const minZoom = 7.5;

// function updateLayerVisibility() {
//     const currentZoom = map.getZoom();

//     if (currentZoom >= minZoom) {
//         if (!map.hasLayer(pointTanksLayer)) map.addLayer(pointTanksLayer);
//         if (!map.hasLayer(technicalTanksLayer)) map.addLayer(technicalTanksLayer);
//     } else {
//         if (map.hasLayer(pointTanksLayer)) map.removeLayer(pointTanksLayer);
//         if (map.hasLayer(technicalTanksLayer)) map.removeLayer(technicalTanksLayer);
//     }
// }

// // Привязываем обновление видимости слоев к событию изменения зума
// map.on('zoomend', updateLayerVisibility);

// // Проверяем начальное состояние видимости
// map.on('load', updateLayerVisibility);

// // Вызываем обновление видимости сразу после инициализации
// updateLayerVisibility();







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
const flowLayerGroup = L.layerGroup();
const minimalistFlowLayerGroup = L.layerGroup();
let flowLayerVisible = false; 
let dataLoaded = false; 

// Убираем слой при загрузке
map.removeLayer(minimalistFlowLayerGroup);
document.getElementById('checkboxOne').checked = false; 

// Настройки направлений для точек (смещения по широте и долготе)
const directionOffsets = {
    1: { lat: 0.5, lng: 0.3 },   // Алашанькоу
    2: { lat: 0.2, lng: 0.5 },    // Атасу
    3: { lat: 0.5, lng: 0.3 },    // ПНХЗ
    4: { lat: 0.5, lng: 0.3 },   // Кумколь
    5: { lat: 0.2, lng: 0.5 },   // Кенкияк
    6: { lat: 0.2, lng: 0.3 },   // ПКОП
    7: { lat: 0.4, lng: 0.2 },   // Шманова
    8: { lat: 0.2, lng: 0.2 },   // Самара
    9: { lat: -0.5, lng: -0.5 },   // Новороссийск
    10: { lat: 0.2, lng: 0.3 },  // Усть-Луга
    11: { lat: 0, lng: 0 }, // Жана Жол
    12: { lat: 0, lng: 0 }, // ПСП 45 
    19: { lat: -0.3, lng: -0.2 },  // Касымова
    24: { lat: -0.3, lng: 0.6 },  // 1235
};

function findFreePosition(coords, layerGroup, pointId) {
    if (pointId === 5) return null; // Исключаем Кенкияк

    const baseOffset = 0.5; // Базовое смещение
    const defaultDirections = [
        [baseOffset, baseOffset],   
        [baseOffset, -baseOffset],  
        [-baseOffset, baseOffset],  
        [-baseOffset, -baseOffset],
        [0, baseOffset],
        [baseOffset, 0],
        [0, -baseOffset],
        [-baseOffset, 0]
    ];

    const customOffset = directionOffsets[pointId] || { lat: 0, lng: 0 };

    // Генерируем последовательность направлений начиная с кастомного, затем добавляем стандартные
    const directions = [
        [customOffset.lat, customOffset.lng],
        ...defaultDirections
    ];

    for (let i = 0; i < directions.length; i++) {
        const offsetLat = directions[i][0];
        const offsetLng = directions[i][1];

        // Чем дальше направление в списке, тем сильнее смещаем (умножаем)
        const scale = 1 + i * 0.2; 

        const candidateCoords = [
            coords[0] + offsetLat * scale,
            coords[1] + offsetLng * scale
        ];

        const isOverlapping = Array.from(layerGroup.getLayers()).some(layer => {
            if (layer.getLatLng) {
                const layerCoords = layer.getLatLng();
                return (
                    Math.abs(layerCoords.lat - candidateCoords[0]) < baseOffset / 2 &&
                    Math.abs(layerCoords.lng - candidateCoords[1]) < baseOffset / 2
                );
            }
            if (layer instanceof L.Polyline) {
                const latlngs = layer.getLatLngs();
                return latlngs.some(latlng =>
                    Math.abs(latlng.lat - candidateCoords[0]) < baseOffset / 2 &&
                    Math.abs(latlng.lng - candidateCoords[1]) < baseOffset / 2
                );
            }
            return false;
        });

        if (!isOverlapping) {
            return candidateCoords;
        }
    }

    // В крайнем случае — ставим далеко
    return [coords[0] + baseOffset * 2, coords[1] + baseOffset * 2];
}


function findFreePositionWithIndex(coords, layerGroup, pointId, usageIndex) {
    if (pointId === 5) return null;

    const baseOffset = 0.5;

    const baseDirections = [
        [1, 1],
        [1, -1],
        [-1, 1],
        [-1, -1],
        [0, 1],
        [1, 0],
        [0, -1],
        [-1, 0]
    ];

    const customOffset = directionOffsets[pointId] || { lat: 0, lng: 0 };
    const hasCustom = customOffset.lat !== 0 || customOffset.lng !== 0;

    // Генерируем направленное смещение
    const directions = hasCustom
        ? [[customOffset.lat, customOffset.lng], ...baseDirections]
        : [...baseDirections];

    const dir = directions[usageIndex % directions.length];
    const scale = 1 + Math.floor(usageIndex / directions.length) * 0.4; // увеличиваем по мере роста

    const candidateCoords = [
        coords[0] + dir[0] * baseOffset * scale,
        coords[1] + dir[1] * baseOffset * scale
    ];

    return candidateCoords;
}



function addMinimalistFlow(points, oilTransferData) {
    minimalistFlowLayerGroup.clearLayers();

    if (!flowLayerVisible) return;

    const uniqueEntries = new Set();  
    const pointUsageCounter = {}; // Счётчик использования точек

    oilTransferData.forEach(record => {
        const isSpecialSource = (record.from_point === 12 || record.from_point === 11);

        // Исключаем Кенкияк как получателя, но не как отправителя
        if (record.to_point === 5) {
            console.log(`⛔ Кенкияк (id 5) исключен, но отправка из ${record.from_point} сохраняется.`);
        }

        const pointId = isSpecialSource ? record.from_point : record.to_point;
        const point = points.find(p => p.id === pointId);

        if (!point || !point.coords) {
            console.warn(`⚠️ Координаты не найдены: ${pointId}`);
            return;
        }

        console.log(`✅ Обрабатываем точку ${pointId}: ${isSpecialSource ? 'Отправка' : 'Прием'} - ${record.from_amount} тн`);

        const recordKey = `${pointId}-${record.from_amount}`;
        if (uniqueEntries.has(recordKey)) return;
        uniqueEntries.add(recordKey);

        // Счётчик использований конкретной точки
        if (!pointUsageCounter[pointId]) pointUsageCounter[pointId] = 0;
        const usageIndex = pointUsageCounter[pointId]++;
        
        // Поиск свободного места с учётом индекса
        const labelPosition = findFreePositionWithIndex(point.coords, minimalistFlowLayerGroup, pointId, usageIndex);
        if (!labelPosition) return;

        const markerHtml = `<div>${record.from_amount} тн</div>`;

        // Черная пунктирная линия
        L.polyline([point.coords, labelPosition], {
            color: 'black',
            weight: 2,
            dashArray: '5, 5',
            opacity: 0.8,
        }).addTo(minimalistFlowLayerGroup);

        // Метка объема
        L.marker(labelPosition, {
            icon: L.divIcon({
                className: 'flow-label',
                html: markerHtml,
                iconSize: null,
                iconAnchor: [4, 18],
            }),
        }).addTo(minimalistFlowLayerGroup);
    });

    map.addLayer(minimalistFlowLayerGroup);
}





// Стили для меток
const style = document.createElement('style');
style.innerHTML = `
.flow-label div {
    font-size: 14px;
    font-weight: bold;
    color: white; 
    text-shadow: 
        -2px -2px 0 black,  
         2px -2px 0 black,
        -2px  2px 0 black,
         2px  2px 0 black,
        -2px  0px 0 black,
         2px  0px 0 black,
         0px -2px 0 black,
         0px  2px 0 black;
    border-radius: 5px; 
    padding: 5px;
    text-align: center;
    white-space: nowrap;
}

.flow-label.sent div {
    color: red !important;
    text-shadow: 
        -2px -2px 0 black,  
         2px -2px 0 black,
        -2px  2px 0 black,
         2px  2px 0 black;
}
`;
document.head.appendChild(style);



// // Инициализация карты
// async function initializeMinimalistFlowMap() {


//     const points = await fetchPointsFromDB();
//     const oilTransferData = await fetchOilTransferFromDB();
//     console.log("✅ Проверяем, есть ли ПСП 45 и Жана Жол в загруженных данных...");

// const psp45Data = oilTransferData.filter(record => record.from_point === 12 || record.to_point === 12);
// const janaJolData = oilTransferData.filter(record => record.from_point === 11 || record.to_point === 11);

// console.log("🔍 ПСП 45:", psp45Data);
// console.log("🔍 Жана Жол:", janaJolData);
//     console.log('Точки:', points);
//     console.log('Данные о нефти:', oilTransferData);

//     if (points.length === 0 || oilTransferData.length === 0) {
//         console.error('Недостаточно данных для отрисовки карты.');
//         return;
//     }

//     addMinimalistFlow(points, oilTransferData);
// }

// document.getElementById('checkboxOne').addEventListener('change', async function () {
//     flowLayerVisible = this.checked;

//     if (flowLayerVisible) {
//         if (!dataLoaded) {
//             await initializeMinimalistFlowMap(); 
//             dataLoaded = true;
//         }
//         map.addLayer(minimalistFlowLayerGroup); 
//     } else {
//         map.removeLayer(minimalistFlowLayerGroup); 
//     }
// });
document.getElementById('checkboxOne').addEventListener('change', async function () {
    flowLayerVisible = this.checked;

    if (flowLayerVisible) {
        if (!dataLoaded) {
            await initializeFlowMap(); // Теперь запускаем объединенную функцию
            dataLoaded = true;
        }
        map.addLayer(minimalistFlowLayerGroup); 
    } else {
        map.removeLayer(minimalistFlowLayerGroup); 
    }
});



//--------------------------------------Сумма для Кенкияка--------------------------------------------
async function fetchKenkiyakOilTotal(year, month) {
    try {
        const response = await fetch(`database/getKenkiyakTotal.php?year=${year}&month=${month}`);
        if (!response.ok) throw new Error(`Ошибка HTTP: ${response.status}`);

        const data = await response.json();
        if (data.error) throw new Error(data.error);

        console.log(`📊 Всего нефти в Кенкияк за ${month}/${year}:`, data.total_oil);
        return data.total_oil;
    } catch (error) {
        console.error('❌ Ошибка загрузки данных по нефти в Кенкияк:', error);
        return 0;
    }
}



async function displayKenkiyakOilTotal(year, month, points) {
    const totalOil = await fetchKenkiyakOilTotal(year, month);
    
    if (totalOil === 0) {
        console.warn("⚠️ Нет данных о нефти для Кенкияка.");
        return;
    }

    // Ищем координаты Кенкияка (id = 5)
    const kenkiyakPoint = points.find(p => p.id === 5);
    if (!kenkiyakPoint || !kenkiyakPoint.coords) {
        console.error("❌ Координаты Кенкияка не найдены!");
        return;
    }

    console.log("📌 Проверка координат Кенкияка:", kenkiyakPoint);

    // Поиск свободного места для метки
    let labelPosition = findFreePosition(kenkiyakPoint.coords, minimalistFlowLayerGroup, 5);
    
    if (!labelPosition) {
        console.warn("⚠️ Используем резервное место для метки Кенкияка.");
        labelPosition = [kenkiyakPoint.coords[0] + 0.5, kenkiyakPoint.coords[1] + 0.5]; 
    }

    console.log(`✅ Метка добавляется в Кенкияк: ${totalOil} тн, позиция:`, labelPosition);

    // Обычная черная линия от Кенкияка к метке
    L.polyline([kenkiyakPoint.coords, labelPosition], {
        color: 'black',
        weight: 2,
        dashArray: '5, 5',
        opacity: 0.8,
    }).addTo(minimalistFlowLayerGroup);

    // Метка с объемом нефти
    L.marker(labelPosition, {
        icon: L.divIcon({
            className: 'flow-label',
            html: `<div>${totalOil} тн</div>`,
            iconSize: null,
            iconAnchor: [4, 18],
        }),
    }).addTo(minimalistFlowLayerGroup);
}





async function initializeFlowMap() {
    const monthInput = document.getElementById('month-input');
    if (!monthInput || !monthInput.value) {
        console.warn("⚠ Невозможно определить текущий месяц для фильтрации.");
        return;
    }

    const [year, month] = monthInput.value.split('-');

    const points = await fetchPointsFromDB();
    const oilTransferData = await fetchOilTransferFromDB(year, month);

    console.log(`📌 Загружены точки:`, points);
    console.log(`📊 Загружены данные за ${year}-${month}:`, oilTransferData);

    clearAllDataLayers(); // очищаем все слои на всякий случай

    if (points.length === 0 || oilTransferData.length === 0) {
        console.warn('❌ Недостаточно данных для отрисовки карты.');
        dataLoaded = false;
        return;
    }

    addMinimalistFlow(points, oilTransferData);
    await displayKenkiyakOilTotal(year, month, points);

    dataLoaded = true;
}





// Проверяем, есть ли уже стили flow-label, чтобы не дублировать
if (!document.getElementById('flow-label-style')) {
    const style = document.createElement('style');
    style.id = 'flow-label-style'; // Устанавливаем ID, чтобы не дублировать
    style.innerHTML = `
    .flow-label div {
        font-size: 14px;
        font-weight: bold;
        color: white; 
        text-shadow: 
            -2px -2px 0 black,  
             2px -2px 0 black,
            -2px  2px 0 black,
             2px  2px 0 black,
            -2px  0px 0 black,
             2px  0px 0 black,
             0px -2px 0 black,
             0px  2px 0 black;
        border-radius: 5px; 
        padding: 5px;
        text-align: center;
        white-space: nowrap;
    }
    `;
    document.head.appendChild(style);
}







// //------------------------------Отображение нефти на трубопроводе-------------------
// // Вызов функции
// (async function initializeOutgoingOilAmounts() {
//     const points = await fetchPointsFromDB();
//     const oilTransferData = await fetchOilTransferFromDB();

//     if (points.length > 0 && oilTransferData.length > 0) {
//         addOutgoingOilAmounts(points, oilTransferData);
//         if (!document.getElementById('checkboxOne').checked) {
//             map.removeLayer(flowLayerGroup); // Если чекбокс выключен, слой изначально скрыт
//         }
//     } else {
//         console.error('Недостаточно данных для отображения исходящих объемов нефти.');
//     }
// })();

// document.getElementById('checkboxOne').addEventListener('change', function () {
//     if (this.checked) {
//         flowLayerGroup.addTo(map); // Показываем слой
//     } else {
//         map.removeLayer(flowLayerGroup); // Скрываем слой
//     }
// });

// // Функция отображения только количества нефти, исходящей из точки
// async function addOutgoingOilAmounts(points, oilTransferData) {
//     outgoingFlowLayerGroup.clearLayers(); // Очищаем только этот слой

//     const staticLabelPositions = {
//         '4-2': [47.5, 69], // От точки 4 к точке 2
//         '4-3': [49.7, 72.4], // От точки 4 к точке 3
//         '4-6': [45.15, 68.65], // От точки 4 к точке 6
//         '5-7': [48.5, 56], // От точки 5 к точке 7
//         '5-4': [48.5, 57.908], // От точки 5 к точке 4
//         '8-9': [52.22, 48], // От точки 8 к точке 9
//         '8-10': [53.2, 49], // От точки 8 к точке 10
//     };

//     const lineColors = {
//         '4-2': 'rgb(3, 198, 252)',
//         '4-3': 'rgb(3, 198, 252)',
//         '4-6': 'rgb(3, 198, 252)',
//         '5-7': 'rgb(221, 5, 221)',
//         '5-4': 'rgb(5, 186, 53)',
//         '8-9': 'rgb(79, 73, 239)',
//         '8-10': 'rgb(79, 73, 239)',
//     };
    
//     const multiOutputPoints = [4, 5, 8]; // Точки с несколькими исходящими потоками

//     multiOutputPoints.forEach(pointId => {
//         // Получаем исходящие записи для точки
//         const outgoingTransfers = oilTransferData.filter(record => record.from_point === pointId);

//         if (outgoingTransfers.length > 0) {
//             const fromPoint = points.find(point => point.id === pointId);

//             if (!fromPoint || !fromPoint.coords) {
//                 console.warn(`Точка с ID ${pointId} не найдена или не имеет координат.`);
//                 return;
//             }

//             outgoingTransfers.forEach(transfer => {
//                 const toPoint = points.find(point => point.id === transfer.to_point);

//                 if (!toPoint || !toPoint.coords) {
//                     console.warn(`Конечная точка с ID ${transfer.to_point} не найдена или не имеет координат.`);
//                     return;
//                 }

//                 // Используем статичные позиции, если они заданы
//                 const staticKey = `${transfer.from_point}-${transfer.to_point}`;
//                 const labelPosition = staticLabelPositions[staticKey] || [
//                     (fromPoint.coords[0] + toPoint.coords[0]) / 2,
//                     (fromPoint.coords[1] + toPoint.coords[1]) / 2,
//                 ];

//                 const labelColor = lineColors[staticKey] || 'black';

//                 // Добавляем только метку с количеством нефти
//                 L.marker(labelPosition, {
//                     icon: L.divIcon({
//                         className: 'flow-label',
//                         html: `<div style="color: ${labelColor}; text-shadow: -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black, 1px 1px 0 black;">${transfer.to_amount} тн</div>`,
//                         iconSize: null,
//                     }),
//                 }).addTo(flowLayerGroup);
//             });
//         }
//     });

//     flowLayerGroup.addTo(map); // Добавляем слой на карту
// }







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
        <td contenteditable="true" data-field="route" title="Путь транспортировки">
            ${row.from_name || 'Источник'} → ${row.to_name || 'Получатель'}
        </td>
        <td contenteditable="true" data-field="amount" title="Объем нефти в тоннах">${row.amount || 0}</td>
        <td contenteditable="true" data-field="losses" title="Потери нефти при транспортировке">${row.losses || 0}</td>
        <td>
            <button class="save-btn">✔️ Сохранить</button>
            <button class="delete-btn">🗑️ Удалить</button>
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

    const routeText = row.querySelector('[data-field="route"]').innerText.split(' → ');
    const updatedData = {
        id: id,
        pointId: pointId,  
        from_name: routeText[0] || '',
        to_name: routeText[1] || '',
        date: new Date().toISOString().split('T')[0], 
        amount: row.querySelector('[data-field="amount"]').innerText.trim(),
        losses: row.querySelector('[data-field="losses"]').innerText.trim(),
    };

    console.log('Отправляем обновленные данные:', updatedData); 

    fetch('database/updateData.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedData)
    })
    .then(response => response.text())
    .then(text => {
        console.log('Ответ сервера:', text); 
        return JSON.parse(text);
    })
    .then(data => {
        if (data.success) {
            alert('Данные обновлены');
        } else {
            alert('Ошибка при обновлении данных: ' + (data.error || 'Неизвестная ошибка'));
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
        pipeline_id: 1, 
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
            newData.id = data.id; 
            addTableRow(newData, null, pointId);
            alert('Запись добавлена');
        } else {
            alert('Ошибка при добавлении записи: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => console.error('Ошибка добавления:', error));
}

let currentPointId = null; 

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

                marker.on('click', () => {
                    currentPointId = point.id; 
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
        addNewRow(currentPointId); 
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

map.removeLayer(flowLayerGroup);
map.removeLayer(minimalistFlowLayerGroup); 
// map.removeLayer(pointTanksLayer);
// map.removeLayer(technicalTanksLayer);

let layersVisible = false; 

filterButton.addEventListener('change', () => { 
    layersVisible = filterButton.checked; 

    if (layersVisible) {
        map.addLayer(flowLayerGroup); 
        map.addLayer(minimalistFlowLayerGroup); 
        // map.addLayer(pointTanksLayer); 
        // map.addLayer(technicalTanksLayer); 
    } else {
        map.removeLayer(flowLayerGroup); 
        map.removeLayer(minimalistFlowLayerGroup);
        // map.removeLayer(pointTanksLayer); 
        // map.removeLayer(technicalTanksLayer); 
    }
});

document.addEventListener("DOMContentLoaded", () => {
    initializeOilFlowMap();
});


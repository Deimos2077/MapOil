// Инициализация карты
var map = L.map('map', {
    center: [51.5, 57], 
    zoom: 5,          
    minZoom: 5,       
    maxZoom: 10,      
    zoomSnap: 0.001,    
    zoomDelta: 0.001,   
    zoomControl: false,
    scrollWheelZoom: false
    })
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
})
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

const mapContainer = document.getElementById('map');

mapContainer.addEventListener('wheel', function (e) {
    const zoom = map.getZoom();
    const delta = e.deltaY;
    const zoomStep = 0.3;

    const atMinZoom = zoom <= map.getMinZoom();
    const atMaxZoom = zoom >= map.getMaxZoom();

    if (delta > 0) {
        // Скролл вниз (отдаление)
        if (!atMinZoom) {
            e.preventDefault(); // не даём странице прокручиваться
            map.setZoom(zoom - zoomStep);
        } else {
            // если уже минимальный зум — скроллим страницу вниз
            window.scrollBy({ top: 100, behavior: 'smooth' });
        }
    } else {
        // Скролл вверх (приближение)
        if (!atMaxZoom) {
            e.preventDefault(); // не даём странице прокручиваться
            map.setZoom(zoom + zoomStep);
        }
        // всегда скроллим страницу вверх
        window.scrollBy({ top: -100, behavior: 'smooth' });
    }
}, { passive: false });


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

const kenkiyakLabelLayer = L.layerGroup().addTo(map);


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
                console.log(`📌 Кенкияк (id 5) получит нефть от ${record.from_point_id}: ${record.from_amount} т`);
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


                // marker.on('click', () => {
                //     console.log(`🔍 Клик по точке ${point.name} (ID: ${point.id})`);
                //     const selectedMonth = document.getElementById('month-input').value;
                //     const [year, month] = selectedMonth.split('-');
                //     openModalWithPointData(point.id, point.name, year, month);
                // });
                
                marker.on('click', () => {
                    console.log(`📍 Всплывающее окно по точке ${point.name} (ID: ${point.id})`);
                    const selectedMonth = document.getElementById('month-input').value;
                    const [year, month] = selectedMonth.split('-');
                    showPointTooltip(point.id, point.name, marker.getLatLng(), year, month); // ✅ Показываем tooltip
                });
                


                
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

function getLineOffsetByZoom(zoom) {
    // Чем больше zoom — тем меньше визуальный сдвиг (в градусах)
    const baseLatOffset = 0.08;
    const baseLngOffset = 0.03;

    const factor = zoom >= 10 ? 0.4 : zoom >= 8 ? 0.6 : zoom >= 6 ? 0.8 : 1;

    return {
        lat: baseLatOffset * factor,
        lng: baseLngOffset * factor
    };
}

function getReservoirSizeByZoom(zoom) {
    if (zoom >= 10) return { width: 30, height: 50 };
    if (zoom >= 8) return { width: 22, height: 40 };
    if (zoom >= 6) return { width: 16, height: 30 };
    return { width: 10, height: 20 };
}

function createReservoirIcon(fillPercent, type, zoom) {
    const size = getReservoirSizeByZoom(zoom, type); 
    const borderColor = type === 1 ? "green" : "rgba(192, 38, 38, 0.99)";
    const borderRadius = type === 1 ? "2px" : "0px";

    const fillColor = "black"; // ✅ всегда чёрный внутри

    return L.divIcon({
        html: `
            <div style="
                position: relative;
                width: ${size.width}px;
                height: ${size.height}px;
                border: 2px solid ${borderColor};
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                background: white;
                transition: width 0.2s ease, height 0.2s ease;
                border-radius: ${borderRadius};
                overflow: hidden;
            ">
                <div style="
                    position: absolute;
                    width: 100%;
                    bottom: 0;
                    height: ${fillPercent}%;
                    background: ${fillColor};
                    transition: height 0.3s ease;
                "></div>
            </div>
        `,
        className: '',
        iconSize: null
    });
}





const reservoirCapacities = {
    1: 5000,
    2: 10000,
    3: 10000,
    4: 10000,
    5: 15000,
    6: 5000,
};


// Создаём слои для резервуаров
const pointTanksLayer = L.layerGroup(); // Точечные резервуары
const technicalTanksLayer = L.layerGroup(); // Технические резервуары
let cachedReservoirs = [];
function addReservoirs(reservoirs) {
    pointTanksLayer.clearLayers();
    technicalTanksLayer.clearLayers();

    const zoom = map.getZoom(); // получаем текущий зум

    reservoirs.forEach(reservoir => {
        const volumeData = {
            start_volume: reservoir.start_volume || 0,
            end_volume: reservoir.end_volume || 0
        };

        const coordStart = [reservoir.lat, reservoir.lng];
        const coordEnd = [reservoir.end_lat, reservoir.end_lng];

        const labelOffsetLng = map.getZoom() >= 8 ? 0.04 : 0.07;

        const offset = reservoirOffsets[reservoir.id] || { start: { lat: 0.05, lng: 0 }, end: { lat: 0.05, lng: 0 } };
        const coordStartLabel = [coordStart[0] + offset.start.lat, coordStart[1] + offset.start.lng];
        const coordEndLabel = [coordEnd[0] + offset.end.lat, coordEnd[1] + offset.end.lng + 0.07];

        const maxCapacity = reservoirCapacities[reservoir.id] || 10000;


        const startFill = getFillPercentage(volumeData.start_volume, maxCapacity);
        const endFill = getFillPercentage(volumeData.end_volume, maxCapacity);

        const layer = reservoir.type === 0 ? pointTanksLayer : technicalTanksLayer;

        // Маркеры с учётом зума
        L.marker(coordStart, {
            icon: createReservoirIcon(startFill, reservoir.type, zoom)
        }).bindPopup(`<strong>${reservoir.name}</strong><br>Остатки на начало: ${volumeData.start_volume} т`)
          .addTo(layer);

        L.marker(coordEnd, {
            icon: createReservoirIcon(endFill, reservoir.type, zoom)
        }).bindPopup(`<strong>${reservoir.name}</strong><br>Остатки на конец: ${volumeData.end_volume} т`)
          .addTo(layer);
        // Заменяем bindPopup на обработчик клика
// L.marker(coordStart, {
//     icon: createReservoirIcon(startFill, reservoir.type, zoom)
// }).on('click', () => {
//     openModalWithReservoirData(reservoir.id, reservoir.name, volumeData.start_volume, volumeData.end_volume);
// }).addTo(layer);

// L.marker(coordEnd, {
//     icon: createReservoirIcon(endFill, reservoir.type, zoom)
// }).on('click', () => {
//     openModalWithReservoirData(reservoir.id, reservoir.name, volumeData.start_volume, volumeData.end_volume);
// }).addTo(layer);


        // Линии
        const centerOffsetLat = 0.035;  // вверх (регулируется по высоте резервуара)
        const centerOffsetLng = 0.025; // вправо (регулируется по вкусу)
        
        const adjustedStart = [coordStart[0] - centerOffsetLat, coordStart[1] + centerOffsetLng];
        const adjustedEnd = [coordEnd[0] - centerOffsetLat, coordEnd[1] + centerOffsetLng];
        
        L.polyline([adjustedStart, adjustedEnd], {
            color: '#722600',
            weight: 4,
            opacity: 0.7
        }).addTo(layer);
        
        

        L.polyline([coordStart, coordStartLabel], {
            color: 'black', weight: 2, opacity: 0.8, dashArray: '4,2'
        }).addTo(layer);

        const shiftedCoordEnd = [coordEnd[0], coordEnd[1] + 0.07]; 

        L.polyline([shiftedCoordEnd, coordEndLabel], {
            color: 'black',
            weight: 2,
            opacity: 0.8,
            dashArray: '4,2'
        }).addTo(layer);

        // Подписи
        L.marker(coordStartLabel, {
            icon: L.divIcon({
                html: `<div style="white-space: nowrap; font-weight: bold; transform: translateY(-10px);"> ${volumeData.start_volume} т</div>`,
                className: ''
            })
        }).addTo(layer);   

        L.marker(coordEndLabel, {
            icon: L.divIcon({
                html: `<div style="white-space: nowrap; font-weight: bold; transform: translateY(-10px);"> ${volumeData.end_volume} т</div>`,
                className: ''
            })
        }).addTo(layer);
    });

    const checkbox = document.getElementById('checkboxTwo');
    if (checkbox && checkbox.checked) {
        map.addLayer(pointTanksLayer);
        map.addLayer(technicalTanksLayer);
    }    
}

// function createReservoirIcon(fillPercent, type, zoom) {
//     const size = getReservoirSizeByZoom(zoom, type); 
//     const borderColor = type === 1 ? "brown" : "rgba(192, 38, 38, 0.99)";
//     const borderRadius = type === 1 ? "2px" : "0px";

//     return L.divIcon({
//         html: `
//             <div style="
//                 position: relative;
//                 width: ${size.width}px;
//                 height: ${size.height}px;
//                 border: 2px solid ${borderColor};
//                 box-shadow: 0 2px 4px rgba(0,0,0,0.3);
//                 background: white;
//                 transition: width 0.2s ease, height 0.2s ease;
//                 border-radius: ${borderRadius};
//                 overflow: hidden;
//             ">
//                 <div style="
//                     position: absolute;
//                     width: 100%;
//                     bottom: 0;
//                     height: ${fillPercent}%;
//                     background: black;
//                     transition: height 0.3s ease;
//                 "></div>
//             </div>
//         `,
//         className: '',
//         iconSize: null
//     });
// }


function getReservoirSizeByZoom(zoom, type) {
    const base = zoom >= 10 ? 1 : zoom >= 8 ? 0.8 : zoom >= 6 ? 0.6 : 0.4;

    if (type === 1) {
        return { width: 45 * base, height: 30 * base }; // технические — почти квадрат
    } else {
        return { width: 25 * base, height: 40 * base }; // точечные — вытянутые
    }
}



//---

    // async function fetchAndRenderReservoirs(year, month) {
    //     currentYear = year;
    //     currentMonth = month;
    
    //     const reservoirs = await fetchReservoirVolumesFromDB(year, month); 
    //     cachedReservoirs = reservoirs;
    //     addReservoirs(reservoirs);
    // }
    window.pointTanksLayer = L.layerGroup();
    window.technicalTanksLayer = L.layerGroup();
    window.reservoirLayerGroup = L.layerGroup();


    window.addEventListener('DOMContentLoaded', () => {
        const monthInput = document.getElementById('month-input');
        const checkbox = document.getElementById('checkboxTwo');
    
        // ✅ Инициализация слоёв, если ещё не созданы
        if (!window.pointTanksLayer) window.pointTanksLayer = L.layerGroup();
        if (!window.technicalTanksLayer) window.technicalTanksLayer = L.layerGroup();
    
        // ✅ Устанавливаем текущую дату, если ничего не выбрано
        if (monthInput && !monthInput.value) {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            monthInput.value = `${year}-${month}`;
        }
    
        // ✅ Загружаем данные по выбранной дате
        const [year, month] = monthInput.value.split('-');
        fetchAndRenderReservoirs(year, month);
    
        // ✅ Обработчик смены даты
        monthInput.addEventListener('change', () => {
            const [year, month] = monthInput.value.split('-');
            fetchAndRenderReservoirs(year, month);
        });
    
        // ✅ Обработчик чекбокса
        checkbox.addEventListener('change', async function () {
            const currentZoom = map.getZoom();
            const zoomThreshold = 6;
        
            if (this.checked) {
                if (currentZoom < zoomThreshold) {
                    console.log("⛔ Масштаб слишком мал — резервуары не будут отображены");
                    return;
                }
        
                const [year, month] = monthInput.value.split('-');
                const reservoirs = await fetchReservoirVolumesFromDB(year, month);
                cachedReservoirs = reservoirs;
        
                addReservoirs(reservoirs); // внутри добавляются к новым слоям
                map.addLayer(pointTanksLayer);
                map.addLayer(technicalTanksLayer);
        
                console.log("✅ Резервуары отображены");
            } else {
                clearReservoirLayers(); 
            }
        });
            
    });
    
    // 👇 Функция очистки всех резервуаров
    function clearReservoirLayers() {
        try {
            map.removeLayer(pointTanksLayer);
            map.removeLayer(technicalTanksLayer);
    
            pointTanksLayer = L.layerGroup();
            technicalTanksLayer = L.layerGroup();
    
            console.log("🧼 Резервуарные слои сброшены и пересозданы");
        } catch (error) {
            console.error("❌ Ошибка при очистке резервуаров:", error);
        }
    }
    

    
    
    document.addEventListener("DOMContentLoaded", () => {
        const checkbox = document.getElementById('checkboxTwo');
        const monthInput = document.getElementById('month-input');
    
        if (!checkbox || !monthInput) {
            console.warn("❌ checkboxTwo или monthInput не найден");
            return;
        }
    
        checkbox.addEventListener('change', async function () {
            const currentZoom = map.getZoom();
            const zoomThreshold = 6;
    
            if (this.checked) {
                if (currentZoom < zoomThreshold) {
                    console.log("⛔ Масштаб слишком мал — резервуары не будут отображены");
                    return;
                }
    
                const [year, month] = monthInput.value.split('-');
                const reservoirs = await fetchReservoirVolumesFromDB(year, month);
                cachedReservoirs = reservoirs;
    
                addReservoirs(reservoirs);
                map.addLayer(pointTanksLayer);
                map.addLayer(technicalTanksLayer);
    
                console.log("✅ Резервуары отображены");
            } else {
                clearReservoirLayers(); // 💥 Должен сработать при отжатии!
            }
        });
    });
    

map.on('zoomend', () => {
    const zoom = map.getZoom();
    const zoomThreshold = 6;

    const checkboxOne = document.getElementById('checkboxOne');
    const checkboxTwo = document.getElementById('checkboxTwo');

    // === 🛢️ Нефть ===
    if (checkboxOne?.checked && window.cachedPoints && window.cachedOilTransferData) {
        map.removeLayer(minimalistFlowLayerGroup);
        if (zoom >= zoomThreshold) {
            addMinimalistFlow(window.cachedPoints, window.cachedOilTransferData);
            displayKenkiyakOilTotal(
                document.getElementById('month-input').value.split('-')[0],
                document.getElementById('month-input').value.split('-')[1],
                window.cachedPoints
            );
            console.log("🛢️ Нефть отображена");
        } else {
            console.log("🛢️ Нефть скрыта — зум ниже порога");
        }
    }

    // === 🛢️ Резервуары ===
    if (checkboxTwo?.checked && window.cachedReservoirs) {
        map.removeLayer(pointTanksLayer);
        map.removeLayer(technicalTanksLayer);

        if (zoom >= zoomThreshold) {
            pointTanksLayer.clearLayers();
            technicalTanksLayer.clearLayers();
            addReservoirs(window.cachedReservoirs);
            console.log("✅ Резервуары отображены");
        } else {
            console.log("⛔ Резервуары скрыты — зум ниже порога");
        }
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


async function main(points, oilTransferData) {
    const pipelinesWithIds = await fetchPipelinesFromDB();

    if (pipelinesWithIds.length === 0 || points.length === 0) {
        console.error("Недостаточно данных для отрисовки линий.");
        return;
    }

    const routes = {
        9: [11, 5, 7, 19, 24, 13, 8, 18, 17, 21, 22, 23],
        10: [12, 5, 7, 19, 24, 13, 8, 16, 20, 23, 10],
        6: [11, 5, 4, 14, 6],
        1: [12, 5, 4, 14, 2, 1],
        3: [11, 5, 4, 14, 2, 3]
    };

    const mainLineColorCache = {}; // Кешировать цвет компании по from-to

    pipelinesWithIds.forEach(({ from, to, company }) => {
        const point1 = points.find(p => p.id === from);
        const point2 = points.find(p => p.id === to);

        if (!point1 || !point2 || !point1.coords || !point2.coords) {
            console.warn(`Проблема с координатами: from=${from}, to=${to}`);
            return;
        }

        const mainLineColor = companyColors[company] || "black";

        const mainLine = L.polyline([point1.coords, point2.coords], {
            pane: 'linesPane',
            color: mainLineColor,
            weight: 6,
            opacity: 0.8,
        }).addTo(map);

        mainLineColorCache[`${from}_${to}`] = mainLineColor;
    });

    // Теперь добавим только нужные пунктирные линии
    Object.entries(routes).forEach(([endPoint, route]) => {
        const hasOil = oilTransferData.some(r => r.to_point === parseInt(endPoint) && r.from_amount > 0);
        if (!hasOil) return;

        for (let i = 0; i < route.length - 1; i++) {
            const from = route[i];
            const to = route[i + 1];

            const point1 = points.find(p => p.id === from);
            const point2 = points.find(p => p.id === to);

            if (!point1 || !point2 || !point1.coords || !point2.coords) continue;

            L.polyline([point1.coords, point2.coords], {
                pane: 'linesPane',
                color: "black",
                weight: 3,
                dashArray: "10, 10",
                opacity: 1,
                className: "dashed-line",
            }).addTo(map);
        }
    });

    // Добавим стрелки (только по прямым из базы)
    pipelinesWithIds.forEach(({ from, to }) => {
        const key = `${from}_${to}`;
        const point1 = points.find(p => p.id === from);
        const point2 = points.find(p => p.id === to);

        const noArrowLines = [
            { from: 12, to: 5 },
            { from: 11, to: 5 },
            { from: 7, to: 19 },
            { from: 24, to: 13 },
            { from: 15, to: 9 }
        ];

        const hasArrow = !noArrowLines.some(line => line.from === from && line.to === to);

        if (point1 && point2 && hasArrow && mainLineColorCache[key]) {
            const mainLine = L.polyline([point1.coords, point2.coords]); // для стрелки
            L.polylineDecorator(mainLine, {
                patterns: [
                    {
                        offset: '50%',
                        repeat: 0,
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,
                            pathOptions: { color: mainLineColorCache[key], fillOpacity: 1 }
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

// 🔁 Слой для визуализации направлений и меток объёмов
window.flowLayerGroup = L.layerGroup(); 
window.minimalistFlowLayerGroup = L.layerGroup();

// 📦 Переменные контроля отображения
let flowLayerVisible = false; 
let dataLoaded = false; 

// ❌ Убираем слой при старте
map.removeLayer(minimalistFlowLayerGroup);
document.getElementById('checkboxOne').checked = false; 

// 🧭 Направления (смещения) для начальной расстановки меток по точкам
const directionOffsets = {
    1: { lat: 0.5, lng: 0.3 },   // Алашанькоу
    2: { lat: -0.6, lng: -0.6 },   // Атасу
    3: { lat: 0.5, lng: 0.3 },   // ПНХЗ
    4: { lat: -0.5, lng: -0.3 }, // Кумколь
    5: { lat: 0.2, lng: 0.5 },   // Кенкияк
    6: { lat: 0.2, lng: 0.3 },   // ПКОП
    7: { lat: 0.4, lng: 0.2 },   // Шманова
    8: { lat: 0.2, lng: 0.2 },   // Самара
    9: { lat: -0.5, lng: -0.5 }, // Новороссийск
    10: { lat: -0.15, lng: 0.4 },  // Усть-Луга
    11: { lat: 0, lng: 0 },      // Жана Жол
    12: { lat: 0, lng: 0 },      // ПСП 45 
    13: { lat: -0.2, lng: -0.4 },// Грушевая
    18: { lat: 0.1, lng: -0.2 }, // Джумангалиева
    19: { lat: -0.3, lng: -0.2 },// Касымова
    20: { lat: -0.2, lng: 0.4 }, // Клин
    21: { lat: -0.2, lng: 0.3 }, // Родионовская
    22: { lat: -0.3, lng: 0.3 }, // Тихорецк
    23: { lat: -0.3, lng: 0.4 }, // Грушевая
    24: { lat: -0.3, lng: 0.6 }, // 1235
    25: { lat: 0.3, lng: 0.3 },  // Никольское
    26: { lat: 0.3, lng: 0.4 },  // Унеча
};

// 📌 Находим свободное место для метки, избегая наложений
function findFreePosition(coords, layerGroup, pointId) {
    if (pointId === 5) return null; // Исключение для Кенкияка, он обрабатывается отдельно

    const baseOffset = 0.6;
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

    const directions = [
        [customOffset.lat, customOffset.lng],
        ...defaultDirections
    ];

    for (let i = 0; i < directions.length; i++) {
        const offsetLat = directions[i][0];
        const offsetLng = directions[i][1];
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

        if (!isOverlapping) return candidateCoords;
    }

    // Крайний случай: размещаем дальше
    return [coords[0] + baseOffset * 2, coords[1] + baseOffset * 2];
}

// 📌 Позиция с учётом количества уже размещённых меток в этой точке
function findFreePositionWithIndex(coords, layerGroup, pointId, usageIndex) {
    const baseOffset = 0.6;
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

    const directions = hasCustom
        ? [[customOffset.lat, customOffset.lng], ...baseDirections]
        : [...baseDirections];

    const dir = directions[usageIndex % directions.length];
    const scale = 1 + Math.floor(usageIndex / directions.length) * 0.4;

    return [
        coords[0] + dir[0] * baseOffset * scale,
        coords[1] + dir[1] * baseOffset * scale
    ];
}


//------------------------Сумма для всех точек-----------------------------
async function displayIntermediateOilTotals(oilTransferData, points) {
    const routes = {
        // Новороссийск
        9: [5, 7, 19, 24, 8, 18, 17, 21, 22, 23],
        // Усть-Луга
        10: [5, 7, 19, 24, 8],
        // ПКОП
        6: [5, 4, 14, 6],
        // Алашанькоу
        1: [5, 4, 14, 2, 1],
        // ПНХЗ
        3: [5, 4, 14, 2, 3]
    };

    const volumesByPoint = {};

    oilTransferData.forEach(record => {
        const route = routes[record.to_point];
        if (!route || route[0] !== 5) return; // Только маршруты от Кенкияка

        route.forEach((pointId, index) => {
            if (index === 0 || index === route.length - 1) return; // Пропускаем Кенкияк и конечную точку

            if (!volumesByPoint[pointId]) volumesByPoint[pointId] = 0;
            volumesByPoint[pointId] += record.from_amount;
        });
    });

    Object.entries(volumesByPoint).forEach(([pointId, volume]) => {
        pointId = parseInt(pointId);
        const point = points.find(p => p.id === pointId);
        if (!point || !point.coords) return;

        const labelPosition = findFreePosition(point.coords, minimalistFlowLayerGroup, pointId);
        if (!labelPosition) return;

        L.polyline([point.coords, labelPosition], {
            color: 'orange',
            weight: 2,
            dashArray: '5, 5',
            opacity: 0.8,
        }).addTo(minimalistFlowLayerGroup);

        L.marker(labelPosition, {
            icon: L.divIcon({
                className: 'flow-label sent',
                html: `<div>${volume.toLocaleString()} т</div>`,
                iconSize: null,
                iconAnchor: [4, 18],
            }),
        }).addTo(minimalistFlowLayerGroup);
    });

    console.log("📦 Объемы на промежуточных точках добавлены:", volumesByPoint);
}




function addMinimalistFlow(points, oilTransferData) {
    const zoom = map.getZoom();
    const zoomThreshold = 6;
    if (zoom < zoomThreshold) return;

    kenkiyakLabelLayer.clearLayers();
    if (!flowLayerVisible) return;

    minimalistFlowLayerGroup.clearLayers();

    const pointUsageCounter = {};
    const allowedPointIds = [1, 3, 6, 9, 10, 11, 12];

    const filteredMap = new Map();
    oilTransferData.forEach(record => {
        const key = `${record.from_point}_${record.to_point}`;
        if (!filteredMap.has(key) || filteredMap.get(key).to_amount < record.to_amount) {
            filteredMap.set(key, record);
        }
    });

    const filteredData = Array.from(filteredMap.values());

// ✅ 1. Отдельно рисуем ПСП 45 и Жана Жол (id = 11, 12)
[11, 12].forEach(specialId => {
    const relevant = oilTransferData.filter(r =>
        r.from_point === specialId &&
        r.to_point === 5 &&
        r.from_amount > 0
    );

    if (relevant.length === 0) {
        console.warn(`⚠️ Нет подходящих записей для точки ${specialId}`);
        return;
    }

    const latestDate = relevant.reduce((latest, r) =>
        new Date(r.date) > new Date(latest) ? r.date : latest,
        relevant[0].date
    );

    const sameDateRecords = relevant.filter(r => r.date === latestDate);

    let totalOut = sameDateRecords.reduce((sum, r) => sum + (parseFloat(r.from_amount) || 0), 0);

    // ✂️ Делим на 2
    totalOut = totalOut / 2;

    if (totalOut === 0) return;

    console.log(`📦 ${specialId === 11 ? 'Жана Жол' : 'ПСП 45'} [id=${specialId}] → 5: ${totalOut} т (дата: ${latestDate}, ÷2)`);

    const point = points.find(p => p.id === specialId);
    if (!point || !point.coords) return;

    const usageIndex = pointUsageCounter[specialId] || 0;
    pointUsageCounter[specialId] = usageIndex + 1;

    const rawLabelPosition = findFreePositionWithIndex(point.coords, minimalistFlowLayerGroup, specialId, usageIndex);
    if (!rawLabelPosition) return;

    const dx = rawLabelPosition[0] - point.coords[0];
    const dy = rawLabelPosition[1] - point.coords[1];
    const length = Math.sqrt(dx * dx + dy * dy);
    if (length === 0) return;

    const unitX = dx / length;
    const unitY = dy / length;
    const baseOffset = 1.8;

    const labelPosition = [
        point.coords[0] + unitX * baseOffset,
        point.coords[1] + unitY * baseOffset
    ];

    const polyline = L.polyline([point.coords, labelPosition], {
        color: 'black',
        weight: 2,
        dashArray: '5, 5',
        opacity: 0.8,
    });

    const marker = L.marker(labelPosition, {
        icon: L.divIcon({
            className: 'flow-label',
            html: `<div><b>${Math.round(totalOut).toLocaleString()} т</b></div>`,
            iconSize: null,
            iconAnchor: [10, 10],
        })
    });

    marker.options._originalPoint = point.coords;
    marker.options._direction = [unitX, unitY];
    marker.options._baseOffset = baseOffset;
    marker.options._polyline = polyline;

    polyline.addTo(minimalistFlowLayerGroup);
    marker.addTo(minimalistFlowLayerGroup);
});


// ✅ 2. Основная визуализация для всех остальных точек из filteredData
filteredData.forEach(record => {
    const isSpecialSource = (record.from_point === 12 || record.from_point === 11);
    const pointId = isSpecialSource ? record.from_point : record.to_point;
    if (pointId === 11 || pointId === 12) return; // эти точки уже отрисованы выше

    if (!allowedPointIds.includes(pointId)) return;

    const point = points.find(p => p.id === pointId);
    if (!point || !point.coords) return;

    const amount = isSpecialSource ? record.from_amount : record.to_amount;
    if (!amount || amount === 0) return;

    if (!pointUsageCounter[pointId]) pointUsageCounter[pointId] = 0;
    const usageIndex = pointUsageCounter[pointId]++;

    const rawLabelPosition = findFreePositionWithIndex(point.coords, minimalistFlowLayerGroup, pointId, usageIndex);
    if (!rawLabelPosition) return;

    const dx = rawLabelPosition[0] - point.coords[0];
    const dy = rawLabelPosition[1] - point.coords[1];
    const length = Math.sqrt(dx * dx + dy * dy);
    if (length === 0) return;

    const unitX = dx / length;
    const unitY = dy / length;
    const baseOffset = 1.5;

    const labelPosition = [
        point.coords[0] + unitX * baseOffset,
        point.coords[1] + unitY * baseOffset
    ];

    const marker = L.marker(labelPosition, {
        icon: L.divIcon({
            className: 'flow-label',
            html: `<div>${amount.toLocaleString()} т</div>`,
            iconSize: null,
            iconAnchor: [10, 10],
        })
    });

    const polyline = L.polyline([point.coords, labelPosition], {
        color: 'black',
        weight: 2,
        dashArray: '5, 5',
        opacity: 0.8,
    });

    marker.options._originalPoint = point.coords;
    marker.options._direction = [unitX, unitY];
    marker.options._baseOffset = baseOffset;
    marker.options._polyline = polyline;

    polyline.addTo(minimalistFlowLayerGroup);
    marker.addTo(minimalistFlowLayerGroup);
});


    // 🔁 Промежуточные суммы
    const routes = {
        9: [5, 7, 19, 24, 8], // Новороссийск
        10: [5, 7, 19, 24, 8],    // Усть-Луга
        6: [5, 4, 14, 6],                         // ПКОП
        1: [5, 4, 14, 2],                      // Алашанькоу
        3: [5, 4, 14, 2, 3]                       // ПНХЗ
    };

    const volumesByPoint = {};
    const logsByPoint = {};
    const handledPairs = new Set();
    
    Object.entries(routes).forEach(([finalPointId, route]) => {
        for (let i = 1; i < route.length; i++) {
            const fromId = route[i - 1];
            const toId = route[i];
            const key = `${fromId}_${toId}`;
    
            // Не обрабатывать один и тот же участок дважды
            if (handledPairs.has(key)) continue;
            handledPairs.add(key);
    
            const record = filteredData.find(r => r.from_point === fromId && r.to_point === toId);
            if (!record) continue;
    
            const isFirst = i === 1;
            const value = isFirst ? record.from_amount : record.to_amount;
    
            if (!volumesByPoint[toId]) {
                volumesByPoint[toId] = 0;
                logsByPoint[toId] = [];
            }
    
            volumesByPoint[toId] += value || 0;
    
            logsByPoint[toId].push({
                from: record.from_point,
                to: record.to_point,
                used: isFirst ? 'from_amount' : 'to_amount',
                value: value || 0,
                full: record
            });
        }
    });
    
    
    
    // 👇 Логирование
    Object.entries(logsByPoint).forEach(([pointId, logs]) => {
        console.log(`📍 Точка ID ${pointId}:`);
        logs.forEach(log => {
            console.log(`   ➕ Из ${log.from} → ${log.to} (${log.used}): ${log.value} т`);
        });
        console.log(`   🧮 Итого в точке ${pointId}: ${volumesByPoint[pointId]} т`);
    });
    

    Object.entries(volumesByPoint).forEach(([pointId, volume]) => {
        pointId = parseInt(pointId);
        const point = points.find(p => p.id === pointId);
        if (!point || !point.coords || volume === 0) return;

        const usageIndex = pointUsageCounter[pointId] || 0;
        pointUsageCounter[pointId] = usageIndex + 1;

        const rawLabelPosition = findFreePositionWithIndex(point.coords, minimalistFlowLayerGroup, pointId, usageIndex);
        if (!rawLabelPosition) return;

        const dx = rawLabelPosition[0] - point.coords[0];
        const dy = rawLabelPosition[1] - point.coords[1];
        const length = Math.sqrt(dx * dx + dy * dy);
        if (length === 0) return;

        const unitX = dx / length;
        const unitY = dy / length;
        const baseOffset = 1.2;

        const labelPosition = [
            point.coords[0] + unitX * baseOffset,
            point.coords[1] + unitY * baseOffset
        ];

        const polyline = L.polyline([point.coords, labelPosition], {
            color: 'black',
            weight: 2,
            dashArray: '5, 5',
            opacity: 0.8,
        });

        const marker = L.marker(labelPosition, {
            icon: L.divIcon({
                className: 'flow-label sent',
                html: `<div>${volume.toLocaleString()} т</div>`,
                iconSize: null,
                iconAnchor: [10, 10],
            })
        });

        marker.options._originalPoint = point.coords;
        marker.options._direction = [unitX, unitY];
        marker.options._baseOffset = baseOffset;
        marker.options._polyline = polyline;

        polyline.addTo(minimalistFlowLayerGroup);
        marker.addTo(minimalistFlowLayerGroup);
    });

    map.addLayer(minimalistFlowLayerGroup);
}





// Стили для меток — единый чёрный стиль
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
    color: white !important;
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



// function addFlowLabel(pointId, value, offset, points) {
//     const point = points.find(p => p.id === pointId);
//     if (!point || !point.coords) {
//         console.error(`❌ Координаты точки ${pointId} не найдены!`);
//         return;
//     }

//     const zoom = map.getZoom();
//     const maxZoom = 10;
//     const minZoom = 5;
//     const relativeZoom = maxZoom - zoom;
//     const scale = Math.min(1 + relativeZoom * 0.2, 2);
//     const minOffsetScale = 0.3;
//     const adjustedScale = Math.max(scale, minOffsetScale);

//     const labelPosition = [
//         point.coords[0] + offset.lat * adjustedScale,
//         point.coords[1] + offset.lng * adjustedScale
//     ];

//     const markerHtml = `<div>${value} тн</div>`;

//     L.polyline([point.coords, labelPosition], {
//         color: 'black',
//         weight: 2,
//         dashArray: '5, 5',
//         opacity: 0.8,
//     }).addTo(minimalistFlowLayerGroup);

//     const marker = L.marker(labelPosition, {
//         icon: L.divIcon({
//             className: 'flow-label',
//             html: markerHtml,
//             iconSize: null,
//             iconAnchor: [10, 10],
//         })
//     });

//     marker.options._originalPoint = point.coords;
//     marker.options._direction = [
//         offset.lat,
//         offset.lng
//     ];

//     marker.addTo(minimalistFlowLayerGroup);
// }


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

    console.log(`✅ Метка добавляется в Кенкияк: ${totalOil} т, позиция:`, labelPosition);

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
            html: `<div>${totalOil} т</div>`,
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

    const [points, oilTransferData, reservoirs] = await Promise.all([
        fetchPointsFromDB(),
        fetchOilTransferFromDB(year, month),
        fetchReservoirVolumesFromDB(year, month)
    ]);
    

    console.log("🛠 Пример oilTransferData[0]:", oilTransferData[0]);
    console.log("🛠 Пример pipelines[0]:", pipelines[0]);

    clearAllDataLayers();

    if (points.length === 0 || oilTransferData.length === 0) {
        console.warn('❌ Недостаточно данных для отрисовки карты.');
        dataLoaded = false;
        return;
    }

    // Основной поток (как раньше)
    addMinimalistFlow(points, oilTransferData);

    // Метка по Кенкияку
    // await displayKenkiyakOilTotal(year, month, points);

    // Промежуточные точки
    const intermediateVolumes = calculateIntermediateOilVolumes(oilTransferData, pipelines);
    console.log("🧭 Промежуточные объемы:", intermediateVolumes);

    Object.entries(intermediateVolumes).forEach(([pointId, volume]) => {
        const point = points.find(p => p.id == pointId);
        if (!point || !point.coords) return;

        const labelPosition = findFreePositionWithIndex(point.coords, minimalistFlowLayerGroup, point.id, 99);

        L.polyline([point.coords, labelPosition], {
            color: 'orange',
            weight: 2,
            dashArray: '5, 5',
            opacity: 0.9,
        }).addTo(minimalistFlowLayerGroup);

        const volumeMarker = L.marker(labelPosition, {
            icon: L.divIcon({
                className: 'flow-label',
                html: `<div>${volume} т</div>`,
                iconSize: null,
                iconAnchor: [4, 18],
            }),
        });
        
        volumeMarker.options._originalPoint = point.coords;
        volumeMarker.options._direction = [
            labelPosition[0] - point.coords[0],
            labelPosition[1] - point.coords[1]
        ];
        
        volumeMarker.addTo(minimalistFlowLayerGroup);
        
    });

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



const filterButton = document.getElementById('checkboxOne');

map.removeLayer(flowLayerGroup);
map.removeLayer(minimalistFlowLayerGroup); 

let layersVisible = false; 

filterButton.addEventListener('change', () => { 
    layersVisible = filterButton.checked; 

    if (layersVisible) {
        map.addLayer(flowLayerGroup); 
        map.addLayer(minimalistFlowLayerGroup); 
    } else {
        map.removeLayer(flowLayerGroup); 
        map.removeLayer(minimalistFlowLayerGroup);
    }
});

document.addEventListener("DOMContentLoaded", () => {
    initializeOilFlowMap();
});


map.on('zoomend', () => {
    const zoom = map.getZoom();
    const zoomThreshold = 6;

    // 🔄 Обновляем позиции всех меток и их линий
    minimalistFlowLayerGroup.eachLayer(layer => {
        if (!layer.options || !layer.options._originalPoint || !layer.options._direction) return;
        if (!layer.options._polyline) return;

        const { _originalPoint, _direction, _polyline, _baseOffset } = layer.options;

        const zoomFactor = Math.pow(2, zoomThreshold - zoom) * _baseOffset;

        const newLatLng = [
            _originalPoint[0] + _direction[0] * zoomFactor,
            _originalPoint[1] + _direction[1] * zoomFactor
        ];

        layer.setLatLng(newLatLng);
        _polyline.setLatLngs([_originalPoint, newLatLng]);
    });

    // 🔁 Обновляем отображение слоя в зависимости от зума и чекбокса
    updateFlowVisibilityByZoom();
});





function updateFlowVisibilityByZoom() {
    const currentZoom = map.getZoom();
    const zoomThreshold = 6;

    if (flowLayerVisible) {
        if (currentZoom >= zoomThreshold) {
            if (!map.hasLayer(minimalistFlowLayerGroup)) {
                map.addLayer(minimalistFlowLayerGroup);
                console.log("🛢️ Нефть отображена");
            }
        } else {
            if (map.hasLayer(minimalistFlowLayerGroup)) {
                map.removeLayer(minimalistFlowLayerGroup);
                console.log("🛢️ Нефть скрыта — зум ниже порога");
            }
        }
    } else {
        if (map.hasLayer(minimalistFlowLayerGroup)) {
            map.removeLayer(minimalistFlowLayerGroup);
            console.log("🔕 Нефть отключена");
        }
    }
}


document.getElementById('checkboxOne').addEventListener('change', async function () {
    flowLayerVisible = this.checked;

    if (!dataLoaded && flowLayerVisible) {
        await initializeFlowMap(); // Загружаем данные
        dataLoaded = true;
    }

    updateFlowVisibilityByZoom(); // Обновляем видимость в зависимости от зума
});

map.on('zoomend', () => {
    updateFlowVisibilityByZoom();
});



//----------------------------------------
function buildGraph(pipelines) {
    const graph = {};
    pipelines.forEach(p => {
        if (!graph[p.from_point_id]) graph[p.from_point_id] = [];
        graph[p.from_point_id].push(p.to_point_id);
    });
    return graph;
}


function findPath(graph, start, end, visited = new Set()) {
    if (start === end) return [start];
    visited.add(start);

    const neighbors = graph[start] || [];
    for (const neighbor of neighbors) {
        if (!visited.has(neighbor)) {
            const path = findPath(graph, neighbor, end, visited);
            if (path) return [start, ...path];
        }
    }
    return null;
}


function calculateIntermediateOilVolumes(oilTransferData, pipelines) {
    const graph = buildGraph(pipelines);
    const flowByPoint = {};

    oilTransferData.forEach(record => {
        const from = record.from_point_id;
        const to = record.to_point_id;
        const amount = record.from_amount;

        const path = findPath(graph, from, to);
        console.log(`🔍 Путь от ${from} к ${to}:`, path);

        if (!path || path.length < 3) return;

        for (let i = 1; i < path.length - 1; i++) {
            const point = path[i];
            if (!flowByPoint[point]) flowByPoint[point] = 0;
            flowByPoint[point] += amount;
        }
    });

    console.log("📦 Суммы промежуточных точек:", flowByPoint);
    return flowByPoint;
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

// // Функция обновления таблицы
// function updateTable(data, pointId) {
//     const tableContainer = document.getElementById('info-table-container');
//     const tableBody = document.getElementById('info-table').querySelector('tbody');

//     // Очищаем таблицу
//     tableBody.innerHTML = '';

//     if (!data || data.length === 0) {
//         tableBody.innerHTML = '<tr><td colspan="6">Нет данных для отображения</td></tr>';
//     } else {
//         data.forEach(row => {
//             addTableRow(row, tableBody, pointId);
//         });
//     }

//     // Показываем таблицу
//     tableContainer.style.display = 'block';
// }

// // Функция для добавления строки в таблицу
// function addTableRow(row, tableBody = null, pointId) {
//     if (!tableBody) {
//         tableBody = document.getElementById('info-table').querySelector('tbody');
//     }

//     const tr = document.createElement('tr');
//     tr.dataset.id = row.id; // Сохранение ID записи

//     tr.innerHTML = `
//         <td contenteditable="true" data-field="route" title="Путь транспортировки">
//             ${row.from_name || 'Источник'} → ${row.to_name || 'Получатель'}
//         </td>
//         <td contenteditable="true" data-field="amount" title="Объем нефти в тоннах">${row.amount || 0}</td>
//         <td contenteditable="true" data-field="losses" title="Потери нефти при транспортировке">${row.losses || 0}</td>
//         <td>
//             <button class="save-btn">✔️ Сохранить</button>
//             <button class="delete-btn">🗑️ Удалить</button>
//         </td>
//     `;

//     // Добавление обработчиков событий
//     tr.querySelector('.save-btn').addEventListener('click', () => saveRow(tr, pointId));
//     tr.querySelector('.delete-btn').addEventListener('click', () => deleteRow(tr));

//     tableBody.appendChild(tr);
// }


// // Функция сохранения изменений
// function saveRow(row, pointId) {
//     const id = row.dataset.id;
//     if (!id) {
//         alert('Ошибка: нет ID записи!');
//         return;
//     }

//     const routeText = row.querySelector('[data-field="route"]').innerText.split(' → ');
//     const updatedData = {
//         id: id,
//         pointId: pointId,  
//         from_name: routeText[0] || '',
//         to_name: routeText[1] || '',
//         date: new Date().toISOString().split('T')[0], 
//         amount: row.querySelector('[data-field="amount"]').innerText.trim(),
//         losses: row.querySelector('[data-field="losses"]').innerText.trim(),
//     };

//     console.log('Отправляем обновленные данные:', updatedData); 

//     fetch('database/updateData.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify(updatedData)
//     })
//     .then(response => response.text())
//     .then(text => {
//         console.log('Ответ сервера:', text); 
//         return JSON.parse(text);
//     })
//     .then(data => {
//         if (data.success) {
//             alert('Данные обновлены');
//         } else {
//             alert('Ошибка при обновлении данных: ' + (data.error || 'Неизвестная ошибка'));
//         }
//     })
//     .catch(error => console.error('Ошибка сохранения:', error));
// }



// // Функция удаления строки
// function deleteRow(row) {
//     const id = row.dataset.id;
//     if (!id) {
//         alert('Ошибка: нет ID записи!');
//         return;
//     }

//     if (!confirm('Вы уверены, что хотите удалить эту запись?')) return;

//     fetch('database/deleteData.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ id: id })
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             row.remove();
//             alert('Запись удалена');
//         } else {
//             alert('Ошибка при удалении');
//         }
//     })
//     .catch(error => console.error('Ошибка удаления:', error));
// }

// function addNewRow(pointId) {
//     const newData = {
//         pointId: pointId,
//         pipeline_id: 1, 
//         date: 'Новая дата',
//         from_name: 'Источник',
//         to_name: 'Получатель',
//         amount: 0,
//         losses: 0
//     };

//     console.log('Данные для отправки:', newData);

//     fetch('database/addData.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify(newData)
//     })
//     .then(response => response.json())
//     .then(data => {
//         console.log('Ответ от сервера:', data);
//         if (data.success) {
//             newData.id = data.id; 
//             addTableRow(newData, null, pointId);
//             alert('Запись добавлена');
//         } else {
//             alert('Ошибка при добавлении записи: ' + (data.error || 'Неизвестная ошибка'));
//         }
//     })
//     .catch(error => console.error('Ошибка добавления:', error));
// }

// let currentPointId = null; 

// fetch('database/getData.php?table=Points')
//     .then(response => response.json())
//     .then(points => {
//         points.forEach(point => {
//             if (point.lat && point.lng) {
//                 const marker = L.circleMarker([point.lat, point.lng], {
//                     pane: 'pointsPane',
//                     radius: 6,
//                     color: 'black',
//                     weight: 2,
//                     fillColor: point.color,
//                     fillOpacity: 1,
//                 }).addTo(map);

//                 marker.on('click', () => {
//                     currentPointId = point.id; 
//                     console.log('Выбрана точка с ID:', currentPointId);

//                     fetch(`database/TableInfo.php?pointId=${point.id}`)
//                         .then(response => response.json())
//                         .then(data => {
//                             updateTable(data, point.id);
//                         })
//                         .catch(error => console.error('Ошибка загрузки данных о точке:', error));
//                 });
//             }
//         });
//     })
//     .catch(error => console.error('Ошибка загрузки данных:', error));

// // Обработчик для кнопки "Добавить запись"
// document.getElementById('add-row-btn').addEventListener('click', () => {
//     console.log('Кнопка "Добавить запись" нажата');
//     if (currentPointId) {
//         addNewRow(currentPointId); 
//     } else {
//         alert('Ошибка: не выбрана точка на карте');
//     }
// });


// //------------------------Модальное окно--------------------------
// function openModalWithPointData(pointId, pointName, year, month) {
//     const modal = document.getElementById('pointModal');
//     const modalBody = document.getElementById('modalBody');
//     const modalTitle = document.getElementById('modalTitle');
//     const blur = document.getElementById('blur-background');

//     modal.style.display = 'block';
//     modal.classList.add('show');
//     blur.classList.add('active');

//     modalTitle.textContent = `Данные по точке: ${pointName}`;
//     modalBody.innerHTML = 'Загрузка...';

//     const url = `database/getPointDetails.php?point_id=${pointId}&year=${year}&month=${month}`;
//     fetch(url)
//         .then(res => res.json())
//         .then(data => {
//             let html = `<p><strong>Принято:</strong> ${data.accepted} т</p>`;
//             html += `<p><strong>Передано:</strong> ${data.transferred} т</p>`;
//             html += `<p><strong>Куда передано:</strong></p><ul>`;
//             data.toPoints.forEach(p => {
//                 html += `<li>${p.name}: ${p.amount} т</li>`;
//             });
//             html += '</ul>';

//             if (data.reservoirs.length > 0) {
//                 html += `<p><strong>Резервуары:</strong></p><ul>`;
//                 data.reservoirs.forEach(r => {
//                     html += `<li>${r.name}: начало ${r.start_volume} т, конец ${r.end_volume} т</li>`;
//                 });
//                 html += '</ul>';
//             } else {
//                 html += `<p>Нет данных по резервуарам</p>`;
//             }

//             modalBody.innerHTML = html;
//         })
//         .catch(() => {
//             modalBody.innerHTML = 'Ошибка загрузки данных';
//         });
// }



// Закрытие по кнопке ✖
document.querySelector('.close-btn').addEventListener('click', closeModal);

// Закрытие по клику вне модального окна
document.addEventListener('click', (e) => {
    const modal = document.getElementById('pointModal');
    const content = document.querySelector('.modal-content');
    const blur = document.getElementById('blur-background');

    if (
        modal.classList.contains('show') &&
        !content.contains(e.target) &&
        !e.target.closest('.modal-content') &&
        (e.target === modal || e.target === blur)
    ) {
        closeModal();
    }
});

// Закрытие по Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

// Функция закрытия
function closeModal() {
    const modal = document.getElementById('pointModal');
    const blur = document.getElementById('blur-background');

    modal.classList.remove('show');
    modal.style.display = 'none';
    blur.classList.remove('active');
}



//------------------------------------------------------------------

function showPreloader() {
    document.getElementById("global-preloader").style.display = "flex";
}

function hidePreloader() {
    document.getElementById("global-preloader").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function () {
    flatpickr("#month-input", {
      locale: "ru",
      dateFormat: "Y-m",
      defaultDate: new Date(),
      allowInput: true,
      clickOpens: true,
      plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "Y-m",   // значение в input
          altFormat: "F Y",    // отображение для пользователя
          theme: "light"
        })
      ]
    });
  
    // На всякий случай вручную открытие
    document.getElementById("month-input").addEventListener("click", function () {
      this._flatpickr.open();
    });
  });
  



//----------------------------------------------
function openModalWithReservoirData(reservoirId, reservoirName) {
    const modal = document.getElementById('pointModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    const blur = document.getElementById('blur-background');

    modal.style.display = 'block';
    modal.classList.add('show');
    blur.classList.add('active');

    modalTitle.textContent = `Информация о резервуаре: ${reservoirName}`;
    modalBody.innerHTML = 'Загрузка...';

    const [year, month] = document.getElementById('month-input').value.split('-');
    const url = `database/getReservoirDetails.php?reservoir_id=${reservoirId}&year=${year}&month=${month}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);

            let html = `<p><strong>Название:</strong> ${data.name}</p>`;
            html += `<p><strong>Тип:</strong> ${data.type == 1 ? 'Технический' : 'Товарный'}</p>`;
            html += `<p><strong>Объем на начало месяца:</strong> ${data.start_volume} т</p>`;
            html += `<p><strong>Объем на конец месяца:</strong> ${data.end_volume} т</p>`;

            modalBody.innerHTML = html;
        })
        .catch((err) => {
            console.error(err);
            modalBody.innerHTML = 'Ошибка загрузки данных о резервуаре';
        });
}

window.addEventListener('load', () => {
    const checkboxOil = document.getElementById('checkboxOne');
    const checkboxTanks = document.getElementById('checkboxTwo');

    if (checkboxOil) {
        checkboxOil.checked = true;
        checkboxOil.dispatchEvent(new Event('change'));
    }

    if (checkboxTanks) {
        checkboxTanks.checked = true;
        checkboxTanks.dispatchEvent(new Event('change'));
    }
});



//-------------mod-point
function showPointTooltip(pointId, pointName, latlng, year, month) {
    const url = `database/getPointDetails.php?point_id=${pointId}&year=${year}&month=${month}`;

    // Только эти точки показывают резервуары
    const pointsWithReservoirs = [12, 7, 4]; // ПСП 45 км, Шманова, Кумколь

    fetch(url)
    .then(res => res.json())
    .then(data => {
        let tooltipContent = `
            <div style="font-size: 14px; line-height: 1.4;">
                <strong>${pointName}</strong><br>
        `;

        // Для ПСП 45 (pointId = 12) и Жанажол (pointId = 11) — только сдача и резервуары
        if ([12, 11].includes(pointId)) {
            tooltipContent += `<strong>Сдано за месяц:</strong> ${data.transferred || 0} т<br>`;
        
            if (data.reservoirs && data.reservoirs.length > 0) {
                data.reservoirs.forEach(r => {
                    tooltipContent += `
                        <strong>Остатки на начало:</strong> ${r.start_volume || 0} т<br>
                        <strong>Остатки на конец:</strong> ${r.end_volume || 0} т<br>
                        <hr style="margin: 4px 0;">
                    `;
                });
            }
        } else {
            // Для всех остальных точек
            tooltipContent += `
                <strong>Принято:</strong> ${data.accepted || 0} т<br>
            `;

            if (pointsWithReservoirs.includes(pointId) && data.reservoirs && data.reservoirs.length > 0) {
                data.reservoirs.forEach(r => {
                    tooltipContent += `
                        <strong>Остатки на начало:</strong> ${r.start_volume || 0} т<br>
                        <strong>Остатки на конец:</strong> ${r.end_volume || 0} т<br>
                        <hr style="margin: 4px 0;">
                    `;
                });
            }
        }

        tooltipContent += `</div>`;

        const tooltip = L.popup({
            closeButton: true,
            offset: [0, -15],
            className: 'point-tooltip'
        })
        .setLatLng(latlng)
        .setContent(tooltipContent)
        .openOn(map);
    })
    .catch(error => {
        console.error('❌ Ошибка при получении данных по точке:', error);
    });

}




// Пример при создании маркера точки:
const marker = L.marker([point.lat, point.lng])
    .addTo(map)
    .on('click', () => {
        showPointTooltip(point.id, point.name, marker.getLatLng(), selectedYear, selectedMonth);
    });

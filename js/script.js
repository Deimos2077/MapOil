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
        points.forEach(point => {
            if (point.lat && point.lng) {
                const marker = L.circleMarker([point.lat, point.lng], {
                    pane: 'pointsPane',
                    radius: 6,             // Размер круга
                    color: 'black',        // Цвет обводки (чёрный)
                    weight: 2,             // Толщина обводки
                    fillColor: point.color, // Цвет заливки из базы данных
                    fillOpacity: 1         // Прозрачность заливки
                }).addTo(map);
                
                const label = L.marker([point.lat + 0.005, point.lng + 0.005], {
                    pane: 'pointsPane',
                    icon: L.divIcon({
                        className: 'marker-label',
                        html: `<div>${point.name}</div>`,
                        iconSize: null,
                        iconAnchor: [60, 15]
                    }),
                }).addTo(map);

                markers.push({ name: point.name, marker, label });
            } else {
                console.warn(`Пропущена точка с ID ${point.id} из-за отсутствия координат.`);
            }
        });
        // Функция для обновления позиций всех меток
        function updateLabels() {
            const labelOffsets = {
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
                "ГНПС им. Б. Джумагалиева": { offsetLat: 0.5, offsetLng: 1.5 },
                "Клин": { offsetLat: 0.15, offsetLng: 0 },
                "915 км н/пр.КЛ": { offsetLat: -0.2, offsetLng: 1.5 },
                "Красноармейск": { offsetLat: -0.1, offsetLng: 1.7 },
                "Родионовская": { offsetLat: -0.1, offsetLng: 1.5 },
                "Тихорецк": { offsetLat: -0.1, offsetLng: 1 },
                "1235,3 км": { offsetLat: -0.07, offsetLng: 1.1 }
            };

            markers.forEach(markerObj => {
                const offsets = labelOffsets[markerObj.name] || { offsetLat: 0, offsetLng: 0 };
                editLabelPosition(markerObj.name, offsets.offsetLat, offsets.offsetLng);
            });

            function editLabelPosition(name, offsetLat, offsetLng, newText = null) {
                const target = markers.find(markerObj => markerObj.name === name);
                if (!target) {
                    console.error(`Точка с именем "${name}" не найдена`);
                    return;
                }
            
                const currentLat = target.marker.getLatLng().lat;
                const currentLng = target.marker.getLatLng().lng;
            
                const newLat = currentLat + offsetLat;
                const newLng = currentLng + offsetLng;
            
                const labelHtml = newText || target.name;
            
                target.label.setLatLng([newLat, newLng]).setIcon(
                    L.divIcon({
                        className: 'marker-label',
                        html: `<div>${labelHtml}</div>`,
                        iconSize: [120, 30],
                        iconAnchor: [60, 15]
                    })
                );
            }
        }
        updateLabels();
    })
    .catch(error => console.error('Ошибка загрузки данных:', error));




//---------------------------Таблица с информацией---------------------------

// Функция обновления таблицы
function updateTable(data) {
    const tableContainer = document.getElementById('info-table-container');
    const tableBody = document.getElementById('info-table').querySelector('tbody');

    // Очищаем таблицу
    tableBody.innerHTML = '';

    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5">Нет данных для отображения</td></tr>';
    } else {
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.date || 'Не указано'}</td>
                <td>${row.from_name || 'Не указано'}</td>
                <td>${row.to_name || 'Не указано'}</td>
                <td>${row.amount || 0}</td>
                <td>${row.losses || 0}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // Показываем таблицу
    tableContainer.style.display = 'block';
}

// Подключение маркеров и обработка событий клика
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

                // Обработчик клика на маркер
                marker.on('click', () => {
                    fetch(`database/TableInfo.php?pointId=${point.id}`)
                        .then(response => response.json())
                        .then(data => {
                            updateTable(data);
                        })
                        .catch(error => console.error('Ошибка загрузки данных о точке:', error));
                });
            } else {
                console.warn(`Пропущена точка с ID ${point.id} из-за отсутствия координат.`);
            }
        });
    })
    .catch(error => console.error('Ошибка загрузки данных:', error));



//--------------------------Резервуары----------------------------

// Создаём кастомные иконки для двух типов резервуаров
const pointReservoirIcon = L.divIcon({
    html: `<div style="width: 25px; 
    height: 40px; 
    background: linear-gradient(to bottom, white 50%, black 50%); 
    border: 2px solid rgb(190, 53, 53); 
    border-radius: 10px; 
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);"></div>`,
    iconSize: [25, 40],
    className: ''
});

const lineReservoirIcon = L.divIcon({
    html: `<div style="width: 40px; 
    height: 25px; 
    background: linear-gradient(to bottom, white 90%, black 10%); 
    border: 2px solid #722600; 
    border-radius: 10px; 
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);"></div>`,
    iconSize: [40, 25],
    className: ''
});

// Создаём слои для резервуаров (но не добавляем на карту)
const pointTanksLayer = L.layerGroup();
const lineTanksLayer = L.layerGroup();

// Получаем данные резервуаров из базы
fetch('database/getData.php?table=Reservoirs')
    .then(response => response.json())
    .then(data => {
        data.forEach(reservoir => {
            if (reservoir.type === 0) {
                // Точечные резервуары
                const coordStart = [reservoir.coords_start_latitude, reservoir.coords_start_longitude];
                const coordEnd = [reservoir.coords_end_latitude, reservoir.coords_end_longitude];

                L.marker(coordStart, { icon: pointReservoirIcon })
                    .bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${coordStart}`)
                    .addTo(pointTanksLayer);

                L.marker(coordEnd, { icon: pointReservoirIcon })
                    .bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${coordEnd}`)
                    .addTo(pointTanksLayer);

                // Добавляем линию между началом и концом
                L.polyline([coordStart, coordEnd], {
                    color: 'rgb(206, 47, 47)',
                    weight: 4,
                    opacity: 0.7
                }).addTo(lineTanksLayer);
            } else if (reservoir.type === 1) {
                // Линейные резервуары
                const startCoord = [reservoir.coords_start_latitude, reservoir.coords_start_longitude];
                const endCoord = [reservoir.coords_end_latitude, reservoir.coords_end_longitude];

                L.marker(startCoord, { icon: lineReservoirIcon })
                    .bindPopup(`<strong>${reservoir.name}</strong><br>Начало: ${startCoord}`)
                    .addTo(lineTanksLayer);

                L.marker(endCoord, { icon: lineReservoirIcon })
                    .bindPopup(`<strong>${reservoir.name}</strong><br>Конец: ${endCoord}`)
                    .addTo(lineTanksLayer);

                // Добавляем линию между началом и концом
                L.polyline([startCoord, endCoord], {
                    color: '#722600',
                    weight: 4,
                    opacity: 0.7
                }).addTo(lineTanksLayer);
            }
        });

        // Обновляем видимость слоев после загрузки данных
        updateLayerVisibility();
    })
    .catch(error => console.error('Ошибка загрузки данных резервуаров:', error));

// Управление видимостью слоев
const minZoom = 7.5;

function updateLayerVisibility() {
    const currentZoom = map.getZoom();

    if (currentZoom >= minZoom) {
        if (!map.hasLayer(pointTanksLayer)) map.addLayer(pointTanksLayer);
        if (!map.hasLayer(lineTanksLayer)) map.addLayer(lineTanksLayer);
    } else {
        if (map.hasLayer(pointTanksLayer)) map.removeLayer(pointTanksLayer);
        if (map.hasLayer(lineTanksLayer)) map.removeLayer(lineTanksLayer);
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

                // Определяем текст для метки (включая источник)
                const sourceText =
                    record.source_type === 'reservoir'
                        ? `<span style="color: red;">(${record.to_amount} тн)</span>`
                        : `<span style="color: brown;">(${record.to_amount} тн)</span>`;

                const markerHtml = `
                    <div>
                        ${record.to_amount} тн ${sourceText}
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




const filterButton = document.getElementById('filter-button');
let flowLayerVisible = true;

filterButton.addEventListener('click', () => {
    flowLayerVisible = !flowLayerVisible;

    // Управляем видимостью слоя
    if (flowLayerVisible) {
        map.addLayer(flowLayerGroup); // Показываем слой
        filterButton.textContent = "Скрыть надписи";
    } else {
        map.removeLayer(flowLayerGroup); // Скрываем слой
        filterButton.textContent = "Показать надписи";
    }
});

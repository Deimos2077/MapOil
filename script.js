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
            
        // Подключаем ползунок к карте
        var slider = document.getElementById('zoom-slider');

        // Обновление карты при изменении значения ползунка
        slider.addEventListener('input', function () {
            var zoomLevel = parseInt(this.value, 10);
            map.setZoom(zoomLevel);
        });

        // Синхронизация ползунка при изменении зума карты
        map.on('zoomend', function () {
            slider.value = map.getZoom();
        });

        // Подключение OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        }).addTo(map);


        L.easyPrint({
    title: 'Распечатать карту',
    position: 'topright', // Позиция кнопки
    sizeModes: ['A4Portrait', 'A4Landscape'], // Поддерживаемые форматы
    exportOnly: false // Установите в false для вызова печати напрямую
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
        fetch('kz_0.json')
            .then(response => response.json())
            .then(data => {
                L.geoJSON(data, { style: geoJsonStyle }).addTo(map);
            });

//----------------------------------Точки(Метки) на карте--------------------------------------
        // Точки и их параметры
        const points = [
            { id: 1, name: "Алашанькоу", coords: [44.916501, 82.628486], color: "red" },
            { id: 2, name: "ГНПС Атасу", coords: [48.65142247900413, 71.61476552101955], color: "red" },
            { id: 3, name: "ПНХЗ", coords: [52.410357662298864, 76.87350912928825], color: "red" },
            { id: 4, name: "ГНПС Кумколь", coords: [46.421120410615245, 65.70359993700806], color: "green" },
            { id: 5, name: "ГНПС Кенкияк", coords: [48.590019, 57.154674], color: "red" },
            { id: 6, name: "ПКОП", coords: [42.324549537481126, 69.36104003337864], color: "red" },
            { id: 7, name: "НПС им. Шманова", coords: [47.153256, 52.311304], color: "green" },
            { id: 8, name: "ПСП Самара", coords: [52.992264, 50.534211], color: "red" },
            { id: 9, name: "Новороссийск", coords: [44.812393, 37.604010], color: "red" },
            { id: 10, name: "Усть-Луга", coords: [59.685555, 28.438980], color: "red" },
            { id: 11, name: "КПОУ Жана Жол", coords: [47.940244, 57.720592], color: "green"},
            { id: 12, name: "ПСП 45 км", coords: [48.905042, 57.235662], color: "green"},
            { id: 13, name: "Большая Черниговка", coords: [52.093603, 50.845903], color: "red" },
            { id: 14, name: "ГНПС им. Б. Джумагалиева", coords: [46.324167, 68.275278], color: "red" },
            { id: 15, name: "Грушовая", coords: [44.705003, 38.032049], color: "red" },
            { id: 16, name: "Клин", coords: [53.129934, 47.562570], color: "red" },
            { id: 17, name: "915 км н/пр.КЛ", coords: [49.441613, 40.205521], color: "red" },
            { id: 18, name: "Красноармейск", coords: [50.999648, 45.745967], color: "red" },
            { id: 19, name: "НПС им. Касымова", coords: [47.212406, 51.850524], color: "red" },
            { id: 20, name: "Никольское", coords: [52.802227, 40.368576], color: "red" },
            { id: 21, name: "Родионовская", coords: [47.588980, 39.675108], color: "red" },
            { id: 22, name: "Тихорецк", coords: [45.843437, 40.180804], color: "red" },
            { id: 23, name: "Унеча", coords: [52.766639, 32.936365], color: "red" },
            { id: 24, name: "1235,3 км", coords: [51.677002, 50.916119], color: "red" }
        ];
// Функция для создания смещения метки
function generateOffset(index) {
    const offsetFactor = 0.002; // Смещение
    return (index % 2 === 0 ? offsetFactor : -offsetFactor) * (index % 3);
}

// Отображение точек с названиями и смещением
points.forEach((point, index) => {
    // Добавление круга для точки
    const marker = L.circleMarker(point.coords, {
        radius: 4,
        color: point.color,
        fillColor: point.color,
        fillOpacity: 0.8
    }).addTo(map);

    // Расчет смещения для метки
    const offsetLat = point.coords[0] + (index % 2 === 0 ? 0.005 : -0.005);
    const offsetLon = point.coords[1] + (index % 3 === 0 ? 0.01 : -0.01);

    // Добавление названия с учетом смещения
    const label = L.marker([offsetLat, offsetLon], {
        icon: L.divIcon({
            className: 'marker-label',
            html: `<div>${point.name}</div>`,
            iconSize: null, // Размеры
            iconAnchor: [60, 15] // Центр текста
        }),
    }).addTo(map);

    // Сохраняем маркер и метку в массив
    markers.push({ name: point.name, marker, label });
});


function editLabelPosition(name, offsetLat, offsetLon, newText = null) {
    // Ищем нужную точку по имени
    const target = markers.find(m => m.name === name);
    if (!target) {
        console.error(`Точка с именем "${name}" не найдена`);
        return;
    }

    // Получаем текущие координаты точки
    const currentLat = target.marker.getLatLng().lat;
    const currentLon = target.marker.getLatLng().lng;

    // Рассчитываем новые координаты для текста метки
    const newLat = currentLat + offsetLat;
    const newLon = currentLon + offsetLon;

    // Обновляем текст метки, если указан новый текст
    const labelHtml = newText || target.name; // Если текст не задан, оставляем старое название

    // Обновляем метку на карте
    target.label.setLatLng([newLat, newLon]).setIcon(
        L.divIcon({
            className: 'marker-label',
            html: `<div>${labelHtml}</div>`,
            iconSize: [120, 30], // Размеры
            iconAnchor: [60, 15] // Центр текста
        })
    );
}
editLabelPosition("ПСП 45 км", 0.15, 0);
editLabelPosition("КПОУ Жана Жол", -0.35, 0);
editLabelPosition("НПС им. Шманова", -0.25, 1.8);
editLabelPosition("НПС им. Касымова", -0.1, -2.1);
editLabelPosition("Новороссийск", 0.1, -1.4);
editLabelPosition("Грушовая", -0.15, 1.15);
editLabelPosition("Унеча", -0.25, 0);
editLabelPosition("Никольское", 0.15, 0);
editLabelPosition("Алашанькоу", -0.1, 1.4);
editLabelPosition("ГНПС Атасу", -0.1, 1.4);
editLabelPosition("ПНХЗ", 0.15, 0);
editLabelPosition("ГНПС Кумколь", -0.35, 0);
editLabelPosition("ГНПС Кенкияк", -0.1, 1.6);
editLabelPosition("ПКОП", -0.4, 0);
editLabelPosition("ПСП Самара", 0, 1.3);
editLabelPosition("Усть-Луга", -0.05, 1.1);
editLabelPosition("Большая Черниговка", 0.1, 1.15);
editLabelPosition("ГНПС им. Б. Джумагалиева", 0.5, 1.5);
editLabelPosition("Клин", 0.15, 0);
editLabelPosition("915 км н/пр.КЛ", -0.2, 1.5);
editLabelPosition("Красноармейск", -0.1, 1.7);
editLabelPosition("Родионовская", -0.1, 1.5);
editLabelPosition("Тихорецк", -0.1, 1);
editLabelPosition("1235,3 км", -0.07, 1.1);


//--------------------------Резервуары----------------------------

const tanksCoords = [
    { 
        name: 'ПСП 45 км', 
        coords: [
            [49, 57.1],
            [49, 57.4]
        ] 
    },
    { 
        name: 'НПС им. Шманова', 
        coords: [
            [46.97, 52.2],
            [46.97, 52.5]
        ] 
    },
    { 
        name: 'ГНПС Кумколь', 
        coords: [
            [46.55, 65.7],
            [46.55, 66]
        ] 
    }
];

// Создаем кастомный divIcon
const cylinderIcon = L.divIcon({
    html: `
        <div style="
            width: 25px;
            height: 40px;
            background: linear-gradient(to bottom, white 50%, black 50%);
            border: 2px solid #2b5a8b;
            border-radius: 10px; /* Закругленные углы */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        ">
        </div>
    `,
    iconSize: [25, 40], // Размер "цилиндра"
    className: '' // Очищаем дефолтные стили
});

// Создаем слой для резервуаров
const tanksLayer = L.layerGroup(
    tanksCoords.flatMap(location => 
        location.coords.map(coord => L.marker(coord, {
            icon: cylinderIcon
        }).bindPopup(`<strong>${location.name}</strong>`)) // Всплывающее окно с названием
    )
);

const minZoomToShow = 7.5; // Минимальный зум для отображения резервуаров

// Логика отображения резервуаров
map.on('zoomend', () => {
    const currentZoom = map.getZoom();

    if (currentZoom >= minZoomToShow) {
        if (!map.hasLayer(tanksLayer)) {
            map.addLayer(tanksLayer); // Показываем резервуары
        }
    } else {
        if (map.hasLayer(tanksLayer)) {
            map.removeLayer(tanksLayer); // Скрываем резервуары
        }
    }
});

// Координаты трубопровода для резервуара в середине
const pipelineReservoirCoords = [
    [47.6, 61.5],
    [47.6, 61.8],
    [47.95, 54.5], 
    [47.95, 54.8]

];

// Создаем новый divIcon для резервуаров другого цвета
const newCylinderIcon = L.divIcon({
    html: `
        <div style="
            width: 40px;
            height: 25px;
            background: linear-gradient(to bottom, white 90%, black 10%);
            border: 2px solid #2b8b5a;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        ">
        </div>
    `,
    iconSize: [40, 60],
    className: ''
});

// Создаем слой для новых резервуаров
const newTanksLayer = L.layerGroup(
    pipelineReservoirCoords.map(coord => L.marker(coord, {
        icon: newCylinderIcon
    }).bindPopup(`<strong>Резервуар на трубопроводе</strong>`)) // Добавляем всплывающее окно
);


function addManualArrows(tanks) {
    tanks.forEach(({ coords, color }) => {
        if (coords.length === 2) {
            const [start, end] = coords;

            // Создаем линию
            L.polyline([start, end], {
                color: color || 'red',
                weight: 4, 
                opacity: 0.9
            }).addTo(arrowLayer);

            // Смещение стрелки (направление вправо от линии)
            const offsetLat = 0; // Сдвиг по широте
            const offsetLng = 0.009; // Сдвиг по долготе

            // Вычисляем точку для стрелки (на 75% пути между началом и концом)
            const arrowPoint = [
                start[0] + 0.75 * (end[0] - start[0]) + offsetLat,
                start[1] + 0.75 * (end[1] - start[1]) + offsetLng
            ];

            // Вычисляем угол для стрелки
            const angle = Math.atan2(end[0] - start[0], end[1] - start[1]) * (180 / Math.PI);

            // Настройки размеров стрелки
            const arrowWidth = 15;  // Ширина стрелки
            const arrowHeight = 25; // Высота стрелки

            // Добавляем стрелку в промежуточную точку
            L.marker(arrowPoint, {
                icon: L.divIcon({
                    html: `
                        <div style="
                            width: 0;
                            height: 0;
                            border-left: ${arrowWidth / 2}px solid transparent;
                            border-right: ${arrowWidth / 2}px solid transparent;
                            border-bottom: ${arrowHeight}px solid ${color || 'red'};
                            transform: rotate(${angle - 270}deg);
                            transform-origin: center center; /* Центрирование стрелки */
                        "></div>
                    `,
                    className: '',
                    iconSize: [arrowWidth, arrowHeight] // Указание размеров
                })
            }).addTo(arrowLayer);
        }
    });
}

// Создаем слой для стрелок
const arrowLayer = L.layerGroup();

// Добавляем стрелки
addManualArrows(
    tanksCoords.map((tank) => ({
        ...tank,
        color: '#679ad2', // Установите цвет стрелки
    }))
);


// Координаты резервуаров в середине нефтепровода
const midPipelineTanks = [
    {
        coords: [[47.6, 61.5], [47.6, 61.8]],
        color: '#88d279'
    },
    {
        coords: [[47.95, 54.5], [47.95, 54.8]],
        color: '#88d279'
    }
];

function addManualArrows(tanks) {
    tanks.forEach(({ coords, color }) => {
        if (coords.length === 2) {
            const [start, end] = coords;

            // Рассчитываем среднюю широту для начала и конца линии
            const adjustedStart = [(start[0] + end[0]) / 2, start[1]]; // Средняя широта
            const adjustedEnd = [(start[0] + end[0]) / 2, end[1]];     // Средняя широта

            // Создаем линию
            L.polyline([adjustedStart, adjustedEnd], {
                color: color || 'red',
                weight: 4,
                opacity: 0.9
            }).addTo(arrowLayer);

            // Смещение стрелки
            const offsetLat = 0; // Сдвиг по широте
            const offsetLng = 0.005; // Сдвиг по долготе (если нужно)

            // Позиция стрелки на 75% от линии
            const arrowPoint = [
                adjustedStart[0] + 0.75 * (adjustedEnd[0] - adjustedStart[0]) + offsetLat,
                adjustedStart[1] + 0.75 * (adjustedEnd[1] - adjustedStart[1]) + offsetLng
            ];

            // Угол стрелки
            const angle = Math.atan2(adjustedEnd[0] - adjustedStart[0], adjustedEnd[1] - adjustedStart[1]) * (180 / Math.PI);

            // Размеры стрелки
            const arrowWidth = 10;
            const arrowHeight = 20;

            // Добавляем стрелку
            L.marker(arrowPoint, {
                icon: L.divIcon({
                    html: `
                        <div style="
                            width: 0;
                            height: 0;
                            border-left: ${arrowWidth / 2}px solid transparent;
                            border-right: ${arrowWidth / 2}px solid transparent;
                            border-bottom: ${arrowHeight}px solid ${color || 'red'};
                            transform: rotate(${angle - 270}deg);
                            transform-origin: center;
                        "></div>
                    `,
                    className: '',
                    iconSize: [arrowWidth, arrowHeight]
                })
            }).addTo(arrowLayer);
        }
    });
}

// Добавляем стрелки для всех резервуаров
addManualArrows(
    midPipelineTanks.map((tank) => ({
        ...tank,
        color: tank.color || '#679ad2'
    }))
);


const minZoomToShowTanks = 7.5; // Минимальный зум для отображения резервуаров
const minZoomToShowNewTanks = 7.5; // Минимальный зум для отображения новых резервуаров
const minZoomToShowArrows = 7.5; // Минимальный зум для отображения стрелок

map.on('zoomend', () => {
    const currentZoom = map.getZoom();

    // Логика отображения резервуаров
    if (currentZoom >= minZoomToShowTanks) {
        if (!map.hasLayer(tanksLayer)) {
            map.addLayer(tanksLayer); // Показываем резервуары
        }
    } else {
        if (map.hasLayer(tanksLayer)) {
            map.removeLayer(tanksLayer); // Скрываем резервуары
        }
    }

    // Логика отображения новых резервуаров
    if (currentZoom >= minZoomToShowNewTanks) {
        if (!map.hasLayer(newTanksLayer)) {
            map.addLayer(newTanksLayer); // Показываем новые резервуары
        }
    } else {
        if (map.hasLayer(newTanksLayer)) {
            map.removeLayer(newTanksLayer); // Скрываем новые резервуары
        }
    }

    // Логика отображения стрелок
    if (currentZoom >= minZoomToShowArrows) {
        if (!map.hasLayer(arrowLayer)) {
            map.addLayer(arrowLayer); // Показываем стрелки
        }
    } else {
        if (map.hasLayer(arrowLayer)) {
            map.removeLayer(arrowLayer); // Скрываем стрелки
        }
    }
});

//--------------------------Линии между точками-------------------------------------
        const pipelinesWithIds = [
    { from: 7, to: 19, company: "АО КазТрансОйл" }, // НПС им. Шманова -> НПС им. Касымова
    { from: 8, to: 16, company: "ПАО Транснефть" }, // ПСП Самара -> Клин
    { from: 16, to: 20, company: "ПАО Транснефть" }, // Клин -> Никольское
    { from: 20, to: 23, company: "ПАО Транснефть" }, // Никольское -> Унеча
    { from: 8, to: 18, company: "ПАО Транснефть" }, // ПСП Самара -> Красноармейск
    { from: 18, to: 17, company: "ПАО Транснефть" }, // Красноармейск -> 915 км н/пр.КЛ
    { from: 17, to: 21, company: "ПАО Транснефть" }, // 915 км н/пр.КЛ -> Родионовская
    { from: 21, to: 22, company: "ПАО Транснефть" }, // Родионовская -> Тихорецк
    { from: 22, to: 15, company: "ПАО Транснефть" }, // Тихорецк -> Грушовая
    { from: 15, to: 9, company: "ПАО Транснефть" }, // Грушовая -> Новороссийск
    { from: 2, to: 1, company: "ТОО «Казахстанско-Китайский трубопровод»" }, // Алашанькоу -> ГНПС Атасу
    { from: 2, to: 3, company: "АО КазТрансОйл" }, // ПНХЗ -> ГНПС Атасу
    { from: 14, to: 2, company: "АО КазТрансОйл" }, // ГНПС Атасу -> ГНПС им. Б. Джумагалиева
    { from: 14, to: 6, company: "АО КазТрансОйл" }, // ПКОП -> ГНПС им. Б. Джумагалиева
    { from: 4, to: 14, company: "АО КазТрансОйл" }, // ГНПС им. Б. Джумагалиева -> ГНПС Кумколь
    { from: 5, to: 4, company: "ТОО «Казахстанско-Китайский трубопровод»" }, // ГНПС Кумколь -> ГНПС Кенкияк
    { from: 11, to: 5, company: "АО КазТрансОйл" }, // КПОУ Жана Жол -> ГНПС Кенкияк
    { from: 12, to: 5, company: "АО КазТрансОйл" }, // ПСП 45 км -> ГНПС Кенкияк
    { from: 5, to: 7, company: "АО 'СЗТК' МунайТас'" }, // ГНПС Кенкияк -> НПС им. Шманова
    { from: 19, to: 24, company: "АО КазТрансОйл" }, // НПС им. Касымова -> 1235,3 км
    { from: 24, to: 13, company: "ПАО Транснефть" }, // 1235,3 км -> Большая Черниговка
    { from: 23, to: 10, company: "ПАО Транснефть" }, // Унеча -> Усть-Луга
    { from: 13, to: 8, company: "ПАО Транснефть" } // Большая Черниговка -> ПСП Самара
];


// Функция для расчёта расстояния (Haversine formula)
function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371.0088; // Средний радиус Земли в километрах
    const toRad = Math.PI / 180; // Конвертация градусов в радианы

    const dLat = (lat2 - lat1) * toRad;
    const dLon = (lon2 - lon1) * toRad;

    const lat1Rad = lat1 * toRad;
    const lat2Rad = lat2 * toRad;

    const a = Math.sin(dLat / 2) ** 2 +
              Math.cos(lat1Rad) * Math.cos(lat2Rad) * Math.sin(dLon / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c; // Расстояние в километрах
}

const companyColors = {
    "АО КазТрансОйл": "rgb(79, 73, 239)",
    "ПАО Транснефть": "rgb(3, 198, 252)",
    "ТОО «Казахстанско-Китайский трубопровод»": "green",
    "АО 'СЗТК' МунайТас'": "rgb(221, 5, 221)"
};

// Добавление линий на карту
pipelinesWithIds.forEach(({ from, to, company }, index) => {
    const point1 = points.find(p => p.id === from);
    const point2 = points.find(p => p.id === to);

    if (point1 && point2) {
        const mainLineColor = companyColors[company] || "black";

        // Основная линия (широкая)
        const mainLine = L.polyline([point1.coords, point2.coords], {
            color: mainLineColor,
            weight: 6, // Увеличенная толщина линии
            opacity: 0.8
        }).addTo(map);

        // Пунктирная линия (анимация)
        const dashedLine = L.polyline([point1.coords, point2.coords], {
            color: "black", // Цвет пунктирной линии
            weight: 3,
            dashArray: "10, 10", // Настройка пунктира: длина штриха и промежутка
            opacity: 1,
            className: "dashed-line" // Класс для CSS-анимации
        }).addTo(map);

        // Добавление стрелок
        const arrowDecorator = L.polylineDecorator(mainLine, {
            patterns: [
                {
                    offset: '50%', // Позиция стрелки (50% от длины линии)
                    repeat: 0, // Не повторять стрелку
                    symbol: L.Symbol.arrowHead({
                        pixelSize: 15, // Размер стрелки
                        pathOptions: { color: mainLineColor, fillOpacity: 1 }
                    })
                }
            ]
        }).addTo(map);
    
    }
});










//-------------------------------Информация хода нефти--------------------------

const pipelinesData = [
    { from: 7, to: 19, oilVolume: 15000 }, // НПС им. Шманова -> НПС им. Касымова
    { from: 8, to: 16, oilVolume: 12000 }, // ПСП Самара -> Клин
    { from: 16, to: 20, oilVolume: 10000 }, // Клин -> Никольское
    { from: 20, to: 23, oilVolume: 14000 }, // Никольское -> Унеча
    { from: 8, to: 18, oilVolume: 11000 }, // ПСП Самара -> Красноармейск
    { from: 18, to: 17, oilVolume: 9000 }, // Красноармейск -> 915 км н/пр.КЛ
    { from: 17, to: 21, oilVolume: 13000 }, // 915 км н/пр.КЛ -> Родионовская
    { from: 21, to: 22, oilVolume: 16000 }, // Родионовская -> Тихорецк
    { from: 22, to: 15, oilVolume: 17000 }, // Тихорецк -> Грушовая
    { from: 15, to: 9, oilVolume: 18000 }, // Грушовая -> Новороссийск
    { from: 2, to: 1, oilVolume: 20000 }, // Алашанькоу -> ГНПС Атасу
    { from: 2, to: 3, oilVolume: 19000 }, // ПНХЗ -> ГНПС Атасу
    { from: 14, to: 2, oilVolume: 21000 }, // ГНПС Атасу -> ГНПС им. Б. Джумагалиева
    { from: 14, to: 6, oilVolume: 22000 }, // ПКОП -> ГНПС им. Б. Джумагалиева
    { from: 4, to: 14, oilVolume: 23000 }, // ГНПС им. Б. Джумагалиева -> ГНПС Кумколь
    { from: 5, to: 4, oilVolume: 24000 }, // ГНПС Кумколь -> ГНПС Кенкияк
    { from: 11, to: 5, oilVolume: 25000 }, // КПОУ Жана Жол -> ГНПС Кенкияк
    { from: 12, to: 5, oilVolume: 26000 }, // ПСП 45 км -> ГНПС Кенкияк
    { from: 5, to: 7, oilVolume: 27000 }, // ГНПС Кенкияк -> НПС им. Шманова
    { from: 19, to: 24, oilVolume: 28000 }, // НПС им. Касымова -> 1235,3 км
    { from: 24, to: 13, oilVolume: 29000 }, // 1235,3 км -> Большая Черниговка
    { from: 23, to: 10, oilVolume: 30000 }, // Унеча -> Усть-Луга
    { from: 13, to: 8, oilVolume: 31000 } // Большая Черниговка -> ПСП Самара
];

pipelinesData.forEach(({ from, to, oilVolume }) => {
    const point1 = points.find(p => p.id === from);
    const point2 = points.find(p => p.id === to);

    if (point1 && point2) {
        // Центр линии
        const midLat = (point1.coords[0] + point2.coords[0]) / 2;
        const midLon = (point1.coords[1] + point2.coords[1]) / 2;

        // Вычисление направления линии
        const dx = point2.coords[1] - point1.coords[1];
        const dy = point2.coords[0] - point1.coords[0];

        // Перпендикулярное смещение
        const length = Math.sqrt(dx * dx + dy * dy);
        const offsetLat = (dy / length) * 0.01; // Масштабировать смещение
        const offsetLon = (-dx / length) * 0.01;

        // Добавление текста
        L.marker([midLat + offsetLat, midLon + offsetLon], {
            icon: L.divIcon({
                className: 'distance-label',
                html: `
                    <div style="
                        font-size: 16px;
                        font-family: Arial, sans-serif;
                        color: black;
                        font-weight: bold;
                        text-align: center;
                        white-space: nowrap;
                    ">
                        ${oilVolume} т
                    </div>
                `,
                iconSize: [0, 0],
                iconAnchor: [0, 0]
            })
        }).addTo(map);
    }
});












//--------------------------------Потери------------------------


// Пример данных с потерями для каждого трубопровода
// Эти данные будут взяты из базы данных в будущем
const pipelineLosses = {
    "7-19": 100, // Потери между НПС им. Шманова и НПС им. Касымова
    "5-7": 50,  // Потери между ПСП Самара и Клин
    "5-4": 75, // Потери между Клин и Никольское
    "14-2": 60, // Потери между Никольское и Унеча
    // Добавьте данные для других трубопроводов
};

const minZoomToShowLossCircles = 7.5; // Минимальный зум для отображения кругляшков потерь

// Создаем слой для кругляшков потерь
const lossCirclesLayer = L.layerGroup();

// Добавляем кругляшки потерь в слой
pipelinesWithIds.forEach(({ from, to }, index) => {
    const point1 = points.find(p => p.id === from);
    const point2 = points.find(p => p.id === to);

    if (point1 && point2) {
        const lineKey = `${from}-${to}`;
        const loss = pipelineLosses[lineKey];

        if (loss !== undefined) {
            const midLat = (point1.coords[0] + point2.coords[0]) / 2;
            const midLon = (point1.coords[1] + point2.coords[1]) / 2;

            const lossCircle = L.divIcon({
                className: 'loss-circle',
                html: `
                    <div style="
                        background: rgba(234, 255, 0, 0.81);
                        color: black;
                        font-size: 12px;
                        font-weight: bold;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border: 2px solid black;
                        box-shadow: 0 0 5px rgba(0,0,0,0.5);
                    ">
                        ${loss}т
                    </div>
                `,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            // Добавляем метку в слой
            L.marker([midLat, midLon], { icon: lossCircle }).addTo(lossCirclesLayer);
        }
    }
});

// Добавляем логику отображения/скрытия при изменении зума
map.on('zoomend', () => {
    const currentZoom = map.getZoom();

    // Логика отображения кругляшков потерь
    if (currentZoom >= minZoomToShowLossCircles) {
        if (!map.hasLayer(lossCirclesLayer)) {
            map.addLayer(lossCirclesLayer); // Показываем кругляшки потерь
        }
    } else {
        if (map.hasLayer(lossCirclesLayer)) {
            map.removeLayer(lossCirclesLayer); // Скрываем кругляшки потерь
        }
    }
});



//--------------------------------------------------------




const filterButton = document.getElementById('filter-button');
let labelsVisible = true;

filterButton.addEventListener('click', () => {
    labelsVisible = !labelsVisible;

    // Управляем видимостью меток объема нефти
    document.querySelectorAll('.distance-label').forEach(label => {
        label.style.display = labelsVisible ? 'block' : 'none';
    });

    // Обновляем текст кнопки
    filterButton.textContent = labelsVisible ? "Скрыть надписи" : "Показать надписи";
});

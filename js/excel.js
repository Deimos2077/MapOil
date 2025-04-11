// загрузка данных Excel
async function loadDataEx() {
    const dateInput = document.getElementById("date-input");
    const date = dateInput.value;

    if (!date) {
        alert("Пожалуйста, выберите дату.");
        return;
    }

    try {
        const response = await fetch(`load_data.php?date=${date}`);
        const result = await response.json();

        if (!result.success) {
            alert("Ошибка: " + result.message);
            return;
        }

        console.log("Загруженные данные:", result);

        const table = document.getElementById("myTable");
        if (!table) {
            alert("Таблица с id='myTable' не найдена.");
            return;
        }

        // Обрабатываем трубопроводы только в таблице myTable
        result.oiltransfers.forEach(row => {
            table.querySelectorAll("tr[data-pipeline-id]").forEach(tr => {
                const pipelineIds = tr.getAttribute("data-pipeline-id")
                    .split(",")
                    .map(id => id.trim());

                const index = pipelineIds.indexOf(String(row.pipeline_id));
                if (index !== -1) {
                    const idx = index + 1; // для from_amount1, losses1, to_amount1 и т.д.

                    const fromSpan = tr.querySelector(`#from_amount${idx}`);
                    const lossSpan = tr.querySelector(`#losses${idx}`);
                    const toSpan = tr.querySelector(`#to_amount${idx}`);

                    if (fromSpan) fromSpan.textContent = row.from_amount ?? "";
                    if (lossSpan) lossSpan.textContent = row.losses ?? "";
                    if (toSpan) toSpan.textContent = row.to_amount ?? "";
                }
            });
        });

        // === РЕЗЕРВУАРЫ ===
        const reservoirsToUse = result.reservoirs.length > 0 ? result.reservoirs : result.last_reservoirs;

        reservoirsToUse.forEach(row => {
            table.querySelectorAll(`tr[reservoir_id="${row.reservoir_id}"]`).forEach(tr => {
                const startCell = tr.querySelector("[id^='start-']");
                const endCell = tr.querySelector("[id^='end-']");

                if (result.reservoirs.length > 0) {
                    // Если есть данные на выбранную дату — используем как есть
                    if (startCell) startCell.textContent = row.start_volume ?? "";
                    if (endCell) endCell.textContent = row.end_volume ?? "";
                } else {
                    // Если данных нет — только start = end из последней записи
                    if (startCell) startCell.textContent = row.end_volume ?? "";
                }
            });
        });

    } catch (error) {
        alert("Ошибка при загрузке данных: " + error);
    }
}

function exportToExcel() {
    let table = document.getElementById("myTable");
    let wb = XLSX.utils.book_new();
    let ws = XLSX.utils.aoa_to_sheet([]);

    // Устанавливаем ширину колонок
    ws['!cols'] = [
        { wpx: 181 }, { wpx: 170 }, { wpx: 86 }, { wpx: 86 }, { wpx: 81 }
    ];

    let rows = table.rows;
    let data = [];
    let merges = [];
    let borderStyle = { // Чёрные границы (толщина 1px)
        top: { style: "thin", color: { rgb: "000000" } },
        bottom: { style: "thin", color: { rgb: "000000" } },
        left: { style: "thin", color: { rgb: "000000" } },
        right: { style: "thin", color: { rgb: "000000" } }
    };

    // Функция для добавления стиля к ячейке
    function setCellStyle(ws, r, c, color, borderStyle) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) ws[cellRef] = { v: "" };

        // Применяем как цвет фона, так и границы
        ws[cellRef].s = {
            fill: { fgColor: { rgb: color } },
            border: borderStyle
        };
    }

    for (let r = 0; r < rows.length; r++) {
        let row = rows[r];
        let rowData = [];
        let colOffset = 0;

        for (let c = 0; c < row.cells.length; c++) {
            let cell = row.cells[c];
            let text = cell.innerText || cell.textContent;
            let cellStyle = { v: text, s: {} };

            // Добавляем обводку только с 3-й строки до 42-й и до колонки W (22-я колонка)
            if (r >= 2 && r <= 41 && c <= 22) {
                cellStyle.s.border = borderStyle;
            }

            // Обработка объединённых ячеек
            let colspan = cell.colSpan || 1;
            let rowspan = cell.rowSpan || 1;

            if (colspan > 1 || rowspan > 1) {
                merges.push({
                    s: { r, c: c + colOffset },
                    e: { r: r + rowspan - 1, c: c + colOffset + colspan - 1 }
                });
            }

            // В 4-й строке C (индекс 2) пустая
            if (r === 3 && c === 2) {
                rowData.push({ v: "" });
            }

            rowData[c + colOffset] = cellStyle;
            colOffset += colspan - 1;
        }

        data.push(rowData);
    }

    // Записываем данные в лист
    XLSX.utils.sheet_add_aoa(ws, data);

    // Применяем объединение ячеек
    ws["!merges"] = merges;
// Объединение ячеек в колонках A и B для строк 8, 15, 26, 30, 31, 36, 38, 39, 40
let mergeRows = [7, 14, 25, 29, 30, 35, 37, 38, 39]; // индексы строк (нумерация с 0)
for (let r of mergeRows) {
    ws["!merges"].push({
        s: { r, c: 0 }, // колонка A (индекс 0)
        e: { r, c: 1 }  // колонка B (индекс 1)
    });
}



    // Голубой фон для строк 3-7, 11, 13, 19, 21, 23, 26 (до W)
    let blueRows = [2, 3, 4,8, 10, 16, 18, 20, 23,];
    for (let r of blueRows) {
        for (let c = 0; c <= 22; c++) {
            setCellStyle(ws, r, c, "DDEBF7", borderStyle); // Голубой фон и чёрные границы
        }
    }

    // Голубой фон для колонок D,E (индексы 3,4) от 8 до 42 строки
    for (let r = 5; r <= 41; r++) {
        for (let c of [3, 4]) {
            setCellStyle(ws, r, c, "DDEBF7", borderStyle); // Голубой фон и чёрные границы
        }
    }

    // Коричневый фон для колонок F,G,H (индексы 5,6,7) от 8-10, 12, 14 строки
    let brownRows = [5, 6, 7, 9,11];
    for (let r of brownRows) {
        for (let c of [5, 6, 7]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }
    let brownRows2 = [5,6,7,9,12,13,14,17];
    for (let r of brownRows2) {
        for (let c of [8, 9, 10]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }
    let brownRows3 = [5,6,7,19,21,22,24,25,26,27,28,29];
    for (let r of brownRows3) {
        for (let c of [11, 12, 13]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }
        
    let brownRows4 = [5, 6, 7, 9];
    for (let r of brownRows4) {
        for (let c of [14, 15, 16]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }
    let brownRows5 = [5, 6, 7, 9,12,13,14,15];
    for (let r of brownRows5) {
        for (let c of [17, 18, 19]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }
    let brownRows6 = [5, 6, 7,19,21,22,24,25,31,32,33,34,35,36,37,38,39];
    for (let r of brownRows6) {
        for (let c of [20, 21, 22]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }

// Объединяем C3 и C4 (ячейка C — индекс 2, строки 3 и 4 — индексы 2 и 3)
ws["!merges"].push({
    s: { r: 2, c: 2 },
    e: { r: 3, c: 2 }
});

// Применяем стили к строкам 3 и 4 (индексы 2 и 3), колонки от A до W (0–22)
for (let r of [0, 2, 3]) {
    for (let c = 0; c <= 22; c++) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) continue;

        // Объединяем стили (wrapText, bold, center, и граница если уже есть)
        ws[cellRef].s = {
            ...ws[cellRef].s,
            alignment: {
                wrapText: true,
                horizontal: "center",
                vertical: "center"
            },
            font: {
                bold: true
            },
            border: ws[cellRef].s?.border || borderStyle // сохраняем границы, если были
        };
    }
}
// Жирный текст в строках 5, 9, 11, 17, 19, 21, 24
let boldRows = [4, 8, 10, 16, 18, 20, 23]; // индексы строк (нумерация с 0)
for (let r of boldRows) {
    for (let c = 0; c <= 22; c++) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) continue;

        ws[cellRef].s = {
            ...ws[cellRef].s,
            font: {
                bold: true
            },
            alignment: ws[cellRef].s?.alignment || {},
            border: ws[cellRef].s?.border || borderStyle
        };
    }
}
// Жирная верхняя обводка (medium) для 3-й строки (индекс 2) до колонки W (индекс 22)
for (let c = 0; c <= 22; c++) {
    let cellRef = XLSX.utils.encode_cell({ r: 2, c });
    if (!ws[cellRef]) continue;

    ws[cellRef].s = {
        ...ws[cellRef].s,
        border: {
            ...ws[cellRef].s?.border, // Сохраняем существующие границы
            top: { style: "medium", color: { rgb: "000000" } } // Жирная верхняя граница
        }
    };
}
// Жирная правая обводка (medium) для колонок B, C, E, H, K, N, Q, T, W (индексы 1, 2, 4, 7, 10, 13, 16, 19, 22)
// от 3-й строки (индекс 2) до 42-й строки (индекс 41)
let thickRightCols = [1, 2, 4, 7, 10, 13, 16, 19, 22]; // Индексы колонок B, C, E, H, K, N, Q, T, W
for (let r = 2; r <= 41; r++) {
    for (let c of thickRightCols) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) continue;

        ws[cellRef].s = {
            ...ws[cellRef].s,
            border: {
                ...ws[cellRef].s?.border, // Сохраняем существующие границы
                right: { style: "medium", color: { rgb: "000000" } } // Жирная правая граница
            }
        };
    }
}
// Жирная верхняя обводка (medium) для 3-й строки (индекс 2) до колонки W (индекс 22)
for (let c = 0; c <= 22; c++) {
    let cellRef = XLSX.utils.encode_cell({ r: 39, c });
    if (!ws[cellRef]) continue;

    ws[cellRef].s = {
        ...ws[cellRef].s,
        border: {
            ...ws[cellRef].s?.border, // Сохраняем существующие границы
            bottom: { style: "medium", color: { rgb: "000000" } } // Жирная верхняя граница
        }
    };
}
// Жирная нижняя обводка (medium) для 42-й строки (индекс 41) от A до W (индексы 0–22)
for (let c = 0; c <= 22; c++) {
    let cellRef = XLSX.utils.encode_cell({ r: 41, c });
    if (!ws[cellRef]) continue;

    ws[cellRef].s = {
        ...ws[cellRef].s,
        border: {
            ...ws[cellRef].s?.border, // Сохраняем существующие границы
            bottom: { style: "medium", color: { rgb: "000000" } } // Жирная нижняя граница
        }
    };
}
// Красный фон для ячеек H12, I18, N23, Q10, R16, W23
let redCells = [
    { r: 11, c: 7 }, // H12 (индексы с нуля)
    { r: 17, c: 8 }, // I18
    { r: 22, c: 13 }, // N23
    { r: 9, c: 16 }, // Q10
    { r: 15, c: 17 }, // R16
    { r: 22, c: 22 }  // W23
];

for (let cell of redCells) {
    let cellRef = XLSX.utils.encode_cell(cell);
    if (!ws[cellRef]) ws[cellRef] = { v: "" };
    ws[cellRef].s = {
        fill: { fgColor: { rgb: "FF0000" } }  // Красный цвет
    };
}
// Применяем шрифт Times New Roman и размер 10 ко всем ячейкам
for (let r = 0; r < rows.length; r++) {
    let row = rows[r];
    for (let c = 0; c < row.cells.length; c++) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) continue;

        // Применяем шрифт ко всем ячейкам
        ws[cellRef].s = {
            ...ws[cellRef].s,
            font: {
                name: "Times New Roman",
                sz: 10
            }
        };
    }
}

    // Добавляем лист в книгу и сохраняем
    XLSX.utils.book_append_sheet(wb, ws, "Таблица");
    XLSX.writeFile(wb, "Расчет Потерь.xlsx");
}
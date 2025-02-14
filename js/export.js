// Экспорт в Excel
document.getElementById('export-excel').addEventListener('click', function () {
    const table = document.querySelector('table'); // Укажите вашу таблицу
    const rows = table.querySelectorAll('tr');
    const data = [];

    rows.forEach(row => {
        const cells = row.querySelectorAll('td, th');
        const rowData = [];
        cells.forEach(cell => {
            rowData.push(cell.innerText); // Сохраняем текст из каждой ячейки
        });
        data.push(rowData);
    });

    // Создаем книгу Excel
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, 'Data');

    // Скачиваем файл
    XLSX.writeFile(wb, 'data.xlsx');
});


// Экспорт в PDF
document.getElementById('export-pdf').addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Заголовок документа
    doc.text('Экспорт таблицы', 10, 10);

    // Получение данных из таблицы
    const table = document.querySelector('table'); // Укажите вашу таблицу
    const rows = table.querySelectorAll('tr');

    let y = 20; // Начальная координата по Y
    rows.forEach(row => {
        const cells = row.querySelectorAll('td, th');
        let x = 10; // Начальная координата по X
        cells.forEach(cell => {
            doc.text(cell.innerText, x, y); // Добавляем текст ячейки в PDF
            x += 50; // Ширина столбца
        });
        y += 10; // Высота строки
    });

    // Скачиваем PDF
    doc.save('data.pdf');
});


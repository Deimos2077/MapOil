// Пример функции для сортировки таблицы по клику на заголовки
document.querySelectorAll('th').forEach((header, index) => {
    header.addEventListener('click', () => {
        sortTable(index);
    });
});

function sortTable(columnIndex) {
    const table = document.getElementById('oil-transfer-table');
    const rows = Array.from(table.rows).slice(1); // Все строки, кроме первой (заголовок)

    const sortedRows = rows.sort((a, b) => {
        const cellA = a.cells[columnIndex].textContent.trim();
        const cellB = b.cells[columnIndex].textContent.trim();

        return cellA.localeCompare(cellB, undefined, { numeric: true });
    });

    table.tBodies[0].append(...sortedRows); // Перемещаем отсортированные строки
}

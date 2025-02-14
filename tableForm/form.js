document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('#data-table tbody');
    const addRowBtn = document.querySelector('#add-row-btn');

    addRowBtn.addEventListener('click', () => {
        const newRow = document.createElement('tr');
        const columns = table.closest('table').querySelectorAll('thead th');
        newRow.innerHTML = Array.from(columns).map((col, index) => {
            return index < columns.length - 1
                ? `<td contenteditable="true"></td>`
                : `<td>
                    <button class="save-btn">Сохранить</button>
                    <button class="delete-btn">Удалить</button>
                   </td>`;
        }).join('');
        table.appendChild(newRow);
    });

    table.addEventListener('click', async (e) => {
        if (e.target.classList.contains('save-btn')) {
            const row = e.target.closest('tr');
            const id = row.dataset.id || '';
            const data = {};
            row.querySelectorAll('td').forEach((td, index) => {
                const columnName = table.closest('table').querySelectorAll('thead th')[index].textContent;
                if (columnName !== 'Действия') {
                    data[columnName] = td.textContent;
                }
            });

            const response = await fetch(`save.php?table=<?= $table ?>`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, ...data })
            });

            if (response.ok) {
                alert('Запись сохранена!');
            } else {
                alert('Ошибка сохранения.');
            }
        } else if (e.target.classList.contains('delete-btn')) {
            const row = e.target.closest('tr');
            const id = row.dataset.id;

            if (confirm('Удалить эту запись?')) {
                const response = await fetch(`delete.php?table=<?= $table ?>&id=${id}`, { method: 'GET' });
                if (response.ok) {
                    row.remove();
                    alert('Запись удалена!');
                } else {
                    alert('Ошибка удаления.');
                }
            }
        }
    });
});

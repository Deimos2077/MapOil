document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('#data-table tbody');
    const addRowBtn = document.querySelector('#add-row-btn');

    // 🔹 Добавление новой строки
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

    // 🔹 Обработчик кнопок "Сохранить" и "Удалить"
    table.addEventListener('click', async (e) => {
        const row = e.target.closest('tr');
        const id = row.dataset.id || '';
        const tableName = new URLSearchParams(window.location.search).get("table") || "oiltransfer";

        if (e.target.classList.contains('save-btn')) {
            // 🔹 Сохранение данных
            const data = { id, table: tableName };
            
            row.querySelectorAll('td').forEach((td, index) => {
                const columnName = table.closest('table').querySelectorAll('thead th')[index].textContent;
                if (columnName !== 'Действия') {
                    data[columnName] = td.textContent.trim();
                }
            });

            console.log("📤 Отправляем данные:", data);

            try {
                const response = await fetch("/mapoilds/MapOil/tableForm/save.php", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                

                const text = await response.text(); // Читаем ответ как текст
                console.log("📥 Ответ сервера (сырой):", text);

                try {
                    const result = JSON.parse(text); // Пробуем разобрать JSON
                    console.log("📥 JSON:", result);
                    
                    if (result.success) {
                        alert('✅ Запись сохранена!');
                        location.reload();
                    } else {
                        alert('❌ Ошибка: ' + result.error);
                    }
                } catch (jsonError) {
                    alert('❌ Ошибка JSON! Сервер вернул не JSON.');
                    console.error("❌ Ошибка парсинга JSON:", jsonError, text);
                }
            } catch (error) {
                console.error("❌ Ошибка сети:", error);
                alert('❌ Ошибка сети! Проверь консоль.');
            }
        } 
        
        else if (e.target.classList.contains('delete-btn')) {
            // 🔹 Удаление записи
            if (!id) {
                row.remove(); // Если ID нет, просто удаляем строку из таблицы
                return;
            }

            if (confirm('❗ Вы уверены, что хотите удалить эту запись?')) {
                try {
                    const response = await fetch(`delete.php?table=${tableName}&id=${id}`, { method: 'GET' });

                    if (response.ok) {
                        row.remove();
                        alert('✅ Запись удалена!');
                    } else {
                        alert('❌ Ошибка удаления.');
                    }
                } catch (error) {
                    console.error("❌ Ошибка сети при удалении:", error);
                    alert('❌ Ошибка сети при удалении!');
                }
            }
        }
    });
});

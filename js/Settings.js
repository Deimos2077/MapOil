

// Размер шрифта
document.getElementById('font-size').addEventListener('input', function () {
    document.body.style.fontSize = `${this.value}px`;
    localStorage.setItem('font-size', this.value);
});

// Отправка отчета по email
document.getElementById('send-report').addEventListener('click', function () {
    const email = document.getElementById('email').value;
    if (email) {
        fetch('/send-report.php', {
            method: 'POST',
            body: JSON.stringify({ email }),
            headers: { 'Content-Type': 'application/json' },
        })
        .then(response => response.json())
        .then(data => alert(data.message || 'Отчет отправлен!'))
        .catch(error => console.error('Ошибка:', error));
    } else {
        alert('Введите email!');
    }
});

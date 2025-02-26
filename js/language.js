document.addEventListener("DOMContentLoaded", function () {
    const languageSelect = document.getElementById("language-select");

    if (!languageSelect) {
        console.error("Ошибка: Не найден селектор смены языка!");
        return;
    }

    // Проверяем сохраненный язык
    const savedLanguage = localStorage.getItem("language") || "ru";
    languageSelect.value = savedLanguage;
    console.log(`🔄 Загружаем язык: ${savedLanguage}`);

    // Загружаем переводы
    loadLanguage(savedLanguage);

    // Смена языка при выборе
    languageSelect.addEventListener("change", function () {
        const selectedLanguage = languageSelect.value;
        localStorage.setItem("language", selectedLanguage);
        console.log(`🌍 Выбран новый язык: ${selectedLanguage}`);
        loadLanguage(selectedLanguage);
    });
});

function loadLanguage(lang) {
    const langFile = `languages/${lang}.json`;
    console.log(`📥 Пытаемся загрузить: ${langFile}`);

    fetch(langFile)
        .then(response => {
            console.log(`🔍 Статус ответа сервера: ${response.status}`);
            return response.text(); // Загружаем как текст
        })
        .then(text => {
            console.log("📄 Полученные данные:", text);
            try {
                const translations = JSON.parse(text); // Пробуем разобрать JSON
                console.log("✅ Файл перевода загружен:", translations);

                document.querySelectorAll("[data-i18n]").forEach(element => {
                    const key = element.getAttribute("data-i18n");
                    if (translations[key]) {
                        element.textContent = translations[key];
                        console.log(`✔ Обновлен текст: ${key} -> ${translations[key]}`);
                    } else {
                        console.warn(`⚠ Предупреждение: Ключ '${key}' отсутствует в файле ${langFile}`);
                    }
                });
            } catch (error) {
                console.error("❌ Ошибка парсинга JSON:", error);
            }
        })
        .catch(error => {
            console.error("🚨 Ошибка загрузки перевода:", error);
        });
}

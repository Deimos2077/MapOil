/*
  Slidemenu
*/
(function() {
    var $body = document.body
        , $menu_trigger = $body.getElementsByClassName('menu-trigger')[0];

    if ( typeof $menu_trigger !== 'undefined' ) {
        $menu_trigger.addEventListener('click', function() {
            $body.className = ( $body.className == 'menu-active' )? '' : 'menu-active';
        });
    }

}).call(this);

// modal content
document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ DOM загружен");

    const modal = document.getElementById("settings-modal");
    const openModalBtn = document.getElementById("settings-toggle");
    const closeModalBtn = document.querySelector(".close");
    
    // Создаем слой размытия
    let blurBackground = document.getElementById("blur-background");
    if (!blurBackground) {
        blurBackground = document.createElement("div");
        blurBackground.id = "blur-background";
        document.body.appendChild(blurBackground);
    }

    if (!modal || !openModalBtn || !closeModalBtn) {
        console.error("❌ Ошибка: один из элементов не найден!");
        return;
    }

    console.log("✅ Все элементы найдены, модальное окно готово к работе!");

    // Открытие модального окна
    openModalBtn.addEventListener("click", function (event) {
        event.preventDefault();
        console.log("🔥 Открываем модальное окно!");
        modal.style.display = "block";
        setTimeout(() => modal.classList.add("show"), 10);
        blurBackground.classList.add("active");
        document.body.classList.add("modal-open");
    });

    // Закрытие модального окна
    function closeModal() {
        console.log("❌ Закрываем модальное окно");
        modal.classList.remove("show");
        blurBackground.classList.remove("active");
        document.body.classList.remove("modal-open");
        setTimeout(() => (modal.style.display = "none"), 300);
    }

    closeModalBtn.addEventListener("click", closeModal);

    // Закрытие при клике вне окна
    blurBackground.addEventListener("click", closeModal);

    // Закрытие через клавишу Escape
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });
});

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
    const modal = document.getElementById("settings-modal");
    const btn = document.getElementById("settings-toggle");
    const closeBtn = document.querySelector(".close");

    // Открываем модальное окно при клике на "Настройки"
    btn.addEventListener("click", function (event) {
        event.preventDefault(); // Предотвращаем переход по ссылке
        modal.style.display = "block";
    });

    // Закрываем модальное окно при клике на "x"
    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Закрываем модальное окно при клике вне его
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});

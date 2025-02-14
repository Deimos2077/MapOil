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

document.addEventListener("DOMContentLoaded", function () {
    const settingsToggle = document.getElementById("settings-toggle");
    const settingsMenu = document.getElementById("settings-menu");

    if (settingsToggle && settingsMenu) {
        settingsToggle.addEventListener("click", function (event) {
            event.preventDefault(); // Предотвращаем переход по ссылке
            settingsMenu.classList.toggle("hidden"); // Переключаем видимость меню
        });
    }
});

<?php
// Подключаем файл для работы с базой данных
include 'database/db.php';

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Карта</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <link rel="stylesheet" href="css/menu.css">
        <style>

        </style>
    </head>
    <body>
    <nav id="slide-menu">
	    <ul>
		    <li class="timeline">Timeline</li>
		    <li class="events">Events</li>
		    <li class="calendar">Calendar</li>
		    <li class="sep settings">Settings</li>
		    <li class="logout">Logout</li>
	    </ul>
    </nav>
    <div id="content">
	<div class="menu-trigger"></div>
    <h1>Карта нефтепроводов</h1>
    <div class="btnList">
        <button id="filter-button">Скрыть надписи</button>
        <ul class="ks-cboxtags">
            <li><input type="checkbox" id="checkboxOne" value="Rainbow Dash"><label for="checkboxOne">Rainbow Dash</label></li>
            <li><input type="checkbox" id="checkboxTwo" value="Cotton Candy" checked><label for="checkboxTwo">Cotton Candy</label></li>
            <li><input type="checkbox" id="checkboxThree" value="Rarity" checked><label for="checkboxThree">Rarity</label></li>
            <li><input type="checkbox" id="checkboxFour" value="Moondancer"><label for="checkboxFour">Moondancer</label></li>
            <li><input type="checkbox" id="checkboxFive" value="Surprise"><label for="checkboxFive">Surprise</label></li>
        </ul>     
    </div>
        <div id="map">
        <div class="legend">
            <strong>Легенда:</strong>
            <ul>
                <li><span class="pipeline" style="background-color: rgb(3, 198, 252);"></span> Нефтепровод АО КазТрансОйл</li>
                <li><span class="pipeline" style="background-color: rgb(79, 73, 239);"></span> Нефтепровод ПАО Транснефть</li>
                <li><span class="pipeline" style="background-color: rgb(5, 186, 53);"></span> Нефтепровод ТОО <br>«Казахстанско-Китайский трубопровод»</li>
                <li><span class="pipeline" style="background-color: rgb(221, 5, 221);"></span> Нефтепровод АО 'СЗТК' МунайТас'</li>
                <li><span class="circle" style="background-color: white; border: 2px solid black;"></span>Приемо-сдаточный пункт</li>
                <li><span class="circle" style="background-color: red; border: 2px solid black;"></span>Приемо-сдаточный пункт с резервуарами для <br>хранения остатков товарной нефти</li>
                <li><span class="cylinder-T" style="background-color: rgb(239, 17, 17)"></span> Резервуар для хранения <br>остатков товарной нефти</li>
                <li><span class="cylinder-S" style="background-color: #88d279;"></span> Нефтепровод для хранения <br>остатков технологической нефти</li>
            </ul>
        </div>
        </div>
        </div>
        <div id="info-table-container" style="margin-top: 20px; display: none;">
             <h3>Информация о передвижении нефти</h3>
             <table id="info-table" style="width: 100%; border-collapse: collapse; border: 1px solid black;">
                 <thead>
                     <tr style="background-color: #f2f2f2;">
                         <th style="border: 1px solid black;">Дата</th>
                         <th style="border: 1px solid black;">Источник</th>
                         <th style="border: 1px solid black;">Получатель</th>
                         <th style="border: 1px solid black;">Количество нефти (тн)</th>
                         <th style="border: 1px solid black;">Потери (тн)</th>
                     </tr>
                 </thead>
                 <tbody>
                 </tbody>
             </table>
    </div>

    <script src= "js/menu.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.7.1/leaflet.polylineDecorator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator/dist/leaflet.polylineDecorator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-easyprint"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.3.2/leaflet.polylineDecorator.min.js"></script>
    <script src="js/script.js"></script>    
</body>
</html>
<?php
// Подключаем файл для работы с базой данных
include 'db.php';

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Карта</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.7.1/leaflet.polylineDecorator.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator/dist/leaflet.polylineDecorator.min.js"></script>


    </head>
    <body>
        <h1>Карта нефтепроводов</h1>
        <div id="map"></div>
        <input id="zoom-slider" type="range" min="3" max="10" value="4" step="0.1" style="position: absolute; top: 25px; left: 550px; z-index: 1000;">
        <button id="filter-button">Скрыть надписи</button>
        <div class="legend">
            <strong>Легенда:</strong>
            <ul>
                <li><span class="pipeline" style="background-color: rgb(79, 73, 239);"></span> Нефтепровод АО КазТрансОйл</li>
                <li><span class="pipeline" style="background-color: rgb(3, 198, 252);"></span> Нефтепровод ПАО Транснефть</li>
                <li><span class="pipeline" style="background-color: green;"></span> Нефтепровод ТОО «Казахстанско-Китайский трубопровод»</li>
                <li><span class="pipeline" style="background-color: rgb(221, 5, 221);"></span> Нефтепровод АО 'СЗТК' МунайТас'</li>
                <li><span class="circle" style="background-color: white; border: 2px solid black;"></span>Приемо-сдаточный пункт</li>
                <li><span class="circle" style="background-color: red; border: 2px solid black;"></span>Приемо-сдаточный пункт с резервуарами для хранения товарной нефти</li>
                <li><span class="cylinder-T" style="background-color: rgb(239, 17, 17)"></span> Резервуар с остатками товарной нефти</li>
                <li><span class="cylinder-S" style="background-color: #88d279;"></span> Нефтепровод с остатками технологической нефти</li>
            </ul>
        </div>        

    <script src="https://cdn.jsdelivr.net/npm/leaflet-easyprint"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.3.2/leaflet.polylineDecorator.min.js"></script>
    <script src="script.js"></script>    
</body>
</html>
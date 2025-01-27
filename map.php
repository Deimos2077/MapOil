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
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.7.1/leaflet.polylineDecorator.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator/dist/leaflet.polylineDecorator.min.js"></script>


    </head>
    <body>
    <input type="checkbox" id="active">
    <label for="active" class="menu-btn"><i class="fas fa-bars"></i></label>
        <div class="wrapper">
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Gallery</a></li>
                <li><a href="#">Feedback</a></li>
            </ul>
        </div>
        <h1>Карта нефтепроводов</h1>
        <div id="map">
        <div class="legend">
            <strong>Легенда:</strong>
            <ul>
                <li><span class="pipeline" style="background-color: rgb(79, 73, 239);"></span> Нефтепровод АО КазТрансОйл</li>
                <li><span class="pipeline" style="background-color: rgb(3, 198, 252);"></span> Нефтепровод ПАО Транснефть</li>
                <li><span class="pipeline" style="background-color: green;"></span> Нефтепровод ТОО «Казахстанско-Китайский трубопровод»</li>
                <li><span class="pipeline" style="background-color: rgb(221, 5, 221);"></span> Нефтепровод АО 'СЗТК' МунайТас'</li>
                <li><span class="circle" style="background-color: white; border: 2px solid black;"></span>Приемо-сдаточный пункт</li>
                <li><span class="circle" style="background-color: red; border: 2px solid black;"></span>Приемо-сдаточный пункт с резервуарами для <br>хранения остатков товарной нефти</li>
                <li><span class="cylinder-T" style="background-color: rgb(239, 17, 17)"></span> Резервуар для хранения остатков товарной нефти</li>
                <li><span class="cylinder-S" style="background-color: #88d279;"></span> Нефтепровод для хранения остатков технологической нефти</li>
            </ul>
        </div>
        </div>
        <button id="filter-button">Скрыть надписи</button>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-easyprint"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.3.2/leaflet.polylineDecorator.min.js"></script>
    <script src="js/script.js"></script>    
</body>
</html>
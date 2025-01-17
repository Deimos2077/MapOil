<?php
// Подключаем файл для работы с базой данных
require_once 'db.php';

// Запрос к базе данных для получения точек
try {
    $sql = "
        SELECT 
            p.id AS point_id, 
            p.name AS point_name, 
            p.latitude, 
            p.longitude, 
            p.color, 
            t.name AS tank_name, 
            t.capacity, 
            t.current_volume
        FROM points p
        LEFT JOIN tanks t ON p.id = t.point_id
    ";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $data = ['error' => $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Карта нефтепроводов</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.7.1/leaflet.polylineDecorator.min.js"></script>
    </head>
    <body>
        <h1>Карта нефтепроводов</h1>
        <div id="map"></div>
        <input id="zoom-slider" type="range" min="3" max="10" value="4" step="0.1" style="position: absolute; top: 25px; left: 550px; z-index: 1000;">
        <button id="filter-button">Скрыть надписи</button>
        
        <!-- Интеграция с серверными данными -->
        <script>
            const pointsData = <?php echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;
        </script>

        <script src="https://cdn.jsdelivr.net/npm/leaflet-easyprint"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.polylineDecorator/1.3.2/leaflet.polylineDecorator.min.js"></script>
        <script src="script.js"></script>    
    </body>
</html>

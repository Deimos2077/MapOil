<?php
require 'database/db.php'; // Подключение к БД

// Получаем JSON данные
$data = json_decode(file_get_contents("php://input"), true);
$pipeline_id = $_GET['pipeline_id'];
$date = $data['date'];

if (!$date || !$pipeline_id) {
    echo "Ошибка: Не указаны дата или pipeline_id!";
    exit;
}

// Извлекаем год и месяц из переданной даты
$year = date('Y', strtotime($date));
$month = date('m', strtotime($date));

foreach ($data['losses'] as $loss) {
    $source = $loss['id']; // ID источника
    $oil_volume = floatval($loss['volume']);
    $loss_coefficient = floatval($loss['percent']);
    $loss_amount = floatval($loss['loss']);

    // Проверяем, существует ли запись за тот же месяц
    $stmt = $pdo->prepare("SELECT id FROM oil_losses WHERE source = ? AND pipeline_id = ? 
                           AND YEAR(date) = ? AND MONTH(date) = ?");
    $stmt->execute([$source, $pipeline_id, $year, $month]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Если запись есть, обновляем её
        $stmt = $pdo->prepare("UPDATE oil_losses SET oil_volume = ?, loss_coefficient = ?, loss_amount = ?, date = ? 
                               WHERE id = ?");
        $stmt->execute([$oil_volume, $loss_coefficient, $loss_amount, $date, $existing['id']]);
    } else {
        // Если записи нет, создаем новую
        $stmt = $pdo->prepare("INSERT INTO oil_losses (source, pipeline_id, oil_volume, loss_coefficient, loss_amount, date) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$source, $pipeline_id, $oil_volume, $loss_coefficient, $loss_amount, $date]);
    }
}

echo "Данные сохранены!";
?>

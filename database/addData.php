<?php
header('Content-Type: application/json');
include 'db.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Ошибка чтения JSON']);
        exit;
    }

    if (!isset($data['pointId'], $data['pipeline_id'], $data['date'], $data['from_name'], $data['to_name'], $data['amount'], $data['losses'])) {
        echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
        exit;
    }

    // Найдем `from_point_id` и `to_point_id` по именам
    $stmt = $pdo->prepare("SELECT id FROM Points WHERE name = ?");
    $stmt->execute([$data['from_name']]);
    $fromPoint = $stmt->fetchColumn();

    $stmt->execute([$data['to_name']]);
    $toPoint = $stmt->fetchColumn();

    if (!$fromPoint || !$toPoint) {
        echo json_encode(['success' => false, 'error' => 'Не найдены точки']);
        exit;
    }

    // Вставка данных
    $stmt = $pdo->prepare("
        INSERT INTO oiltransfer (from_point_id, to_point_id, pipeline_id, date, to_amount, losses)
        VALUES (:from_point_id, :to_point_id, :pipeline_id, :date, :amount, :losses)
    ");
    $stmt->execute([
        ':from_point_id' => $fromPoint,
        ':to_point_id' => $toPoint,
        ':pipeline_id' => $data['pipeline_id'],
        ':date' => $data['date'],
        ':amount' => $data['amount'],
        ':losses' => $data['losses'],
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

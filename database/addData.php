<?php
header('Content-Type: application/json');
include 'db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['pointId'], $data['pipeline_id'], $data['date'], $data['from_name'], $data['to_name'], $data['amount'], $data['losses'])) {
        echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO oiltransfer (from_point_id, to_point_id, pipeline_id, date, to_amount, losses)
        VALUES (:from_point_id, :to_point_id, :pipeline_id, :date, :amount, :losses)
    ");
    $stmt->execute([
        ':from_point_id' => $data['pointId'], // Используем существующий pointId
        ':to_point_id' => $data['pointId'],  // Замените, если требуется другой ID
        ':pipeline_id' => $data['pipeline_id'], // Используем pipeline_id
        ':date' => $data['date'],
        ':amount' => $data['amount'],
        ':losses' => $data['losses'],
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
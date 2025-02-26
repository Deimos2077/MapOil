<?php
header('Content-Type: application/json');
include 'db.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['id'], $data['from_name'], $data['to_name'], $data['date'], $data['amount'], $data['losses'])) {
        echo json_encode(['error' => 'Некорректные входные данные']);
        exit;
    }

    // Найдём ID отправной и конечной точки
    $stmt = $pdo->prepare("SELECT id FROM Points WHERE name = ?");
    
    $stmt->execute([$data['from_name']]);
    $fromPoint = $stmt->fetchColumn();

    $stmt->execute([$data['to_name']]);
    $toPoint = $stmt->fetchColumn();

    if (!$fromPoint || !$toPoint) {
        echo json_encode(['error' => 'Ошибка: одна из точек не найдена']);
        exit;
    }

    // Обновляем данные в таблице
    $stmt = $pdo->prepare("
        UPDATE oiltransfer 
        SET date = ?, 
            from_point_id = ?, 
            to_point_id = ?, 
            to_amount = ?, 
            losses = ?
        WHERE id = ?
    ");
    $stmt->execute([$data['date'], $fromPoint, $toPoint, $data['amount'], $data['losses'], $data['id']]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка SQL: ' . $e->getMessage()]);
}
?>

<?php
// database/updateData.php
header('Content-Type: application/json');
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE oiltransfer SET date = ?, from_point_id = (SELECT id FROM Points WHERE name = ?), to_point_id = (SELECT id FROM Points WHERE name = ?), to_amount = ?, losses = ? WHERE id = ?");
        $stmt->execute([$data['date'], $data['from_name'], $data['to_name'], $data['amount'], $data['losses'], $data['id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Нет данных для обновления']);
}

?>
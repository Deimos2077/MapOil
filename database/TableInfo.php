<?php
header('Content-Type: application/json');
include 'db.php';

$pointId = $_GET['pointId'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.date,
            p1.name AS from_name,
            p2.name AS to_name,
            t.to_amount AS amount,
            t.losses
        FROM oiltransfer t
        LEFT JOIN Points p1 ON t.from_point_id = p1.id
        LEFT JOIN Points p2 ON t.to_point_id = p2.id
        WHERE t.from_point_id = :pointId OR t.to_point_id = :pointId
    ");
    $stmt->execute(['pointId' => $pointId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
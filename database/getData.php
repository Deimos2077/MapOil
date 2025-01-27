<?php
// getData.php
header('Content-Type: application/json');
include 'db.php';

$tableName = $_GET['table']; // имя таблицы передается через GET параметр

try {
    $stmt = $pdo->prepare("SELECT * FROM $tableName");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

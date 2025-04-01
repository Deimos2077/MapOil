<?php
header('Content-Type: application/json');
include 'db.php';

$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;

try {
    if ($year && $month) {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.name,
                r.type,
                r.coords_start_latitude AS lat,
                r.coords_start_longitude AS lng,
                r.coords_end_latitude AS end_lat,
                r.coords_end_longitude AS end_lng,
                v.start_volume,
                v.end_volume
            FROM Reservoirs r
            LEFT JOIN (
                SELECT v1.*
                FROM reservoirvolumes v1
                INNER JOIN (
                    SELECT reservoir_id, MAX(date) AS max_date
                    FROM reservoirvolumes
                    WHERE YEAR(date) = :year AND MONTH(date) = :month
                    GROUP BY reservoir_id
                ) v2 ON v1.reservoir_id = v2.reservoir_id AND v1.date = v2.max_date
            ) v ON r.id = v.reservoir_id
        ");

        $stmt->execute(['year' => $year, 'month' => $month]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

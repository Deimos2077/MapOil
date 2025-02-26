<?php
include('database/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $column = $_POST["column"];
    $value = $_POST["value"];

    try {
        $stmt = $pdo->prepare("UPDATE oil_report_values SET $column = :value WHERE id = :id");
        $stmt->execute(["value" => $value, "id" => $id]);
        echo "✅ Данные успешно обновлены.";
    } catch (PDOException $e) {
        echo "❌ Ошибка обновления: " . $e->getMessage();
    }
}
?>

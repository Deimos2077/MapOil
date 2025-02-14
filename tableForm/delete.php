<?php
require 'C:\xampp\htdocs\MapOil\database\db.php';

$table = $_GET['table'];
$allowedTables = ['oiltransfer', 'pipelines', 'points', 'reservoirs', 'reservoirvolumes'];
if (!in_array($table, $allowedTables)) {
    die('Недопустимая таблица.');
}

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
$stmt->execute([$id]);
?>

<?php
require 'vendor/autoload.php'; // Подключаем PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['excel_file']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $filePath)) {
        echo "Файл успешно загружен: $fileName<br>";

        try {
            $pdo = new PDO('mysql:host=192.168.1.23:3306;dbname=mapoil;charset=utf8', 'user', 'oil4815162342');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getSheet(12); // Берем 13-ю страницу (индексация с 0)

            $sql = "INSERT INTO shipments (date, customer, gross_tons, net_tons, avg_price, discount, final_price, price_per_ton, total_amount, barrel_ratio, net_barrels, total_tenge, price_tenge, exchange_rate)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            foreach ($worksheet->getRowIterator(2) as $row) {
                $data = [];

                $data[] = $worksheet->getCell('A' . $row->getRowIndex())->getFormattedValue(); // Дата отгрузки
                $data[] = $worksheet->getCell('B' . $row->getRowIndex())->getFormattedValue(); // Покупатель
                $data[] = (float)$worksheet->getCell('C' . $row->getRowIndex())->getCalculatedValue(); // Тонны брутто
                $data[] = (float)$worksheet->getCell('D' . $row->getRowIndex())->getCalculatedValue(); // Тонны нетто
                $data[] = (float)$worksheet->getCell('E' . $row->getRowIndex())->getCalculatedValue(); // Средняя котировка
                $data[] = (float)$worksheet->getCell('F' . $row->getRowIndex())->getCalculatedValue(); // Дисконт
                $data[] = (float)$worksheet->getCell('G' . $row->getRowIndex())->getCalculatedValue(); // Окончательная цена
                $data[] = (float)$worksheet->getCell('H' . $row->getRowIndex())->getCalculatedValue(); // Цена за тонну
                $data[] = (float)$worksheet->getCell('I' . $row->getRowIndex())->getCalculatedValue(); // Сумма поставки
                $data[] = (float)$worksheet->getCell('J' . $row->getRowIndex())->getCalculatedValue(); // Коэффициент баррелизации
                $data[] = (float)$worksheet->getCell('K' . $row->getRowIndex())->getCalculatedValue(); // Количество баррелей нетто
                $data[] = (float)$worksheet->getCell('L' . $row->getRowIndex())->getCalculatedValue(); // Сумма поставки (тенге без НДС)
                $data[] = (float)$worksheet->getCell('M' . $row->getRowIndex())->getCalculatedValue(); // Цена за тонну (тенге без НДС)
                $data[] = (float)$worksheet->getCell('N' . $row->getRowIndex())->getCalculatedValue(); // Курс $

                if (empty($data[0]) || strtotime($data[0]) === false) continue; // Пропуск пустых строк
                if (date('m', strtotime($data[0])) > 12) continue; // Исключение данных после декабря

                $stmt->execute($data); // Вставляем данные
            }

            echo "Импорт завершен!";
        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка файла Excel</title>
</head>
<body>
    <h2>Загрузка Excel-файла</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="excel_file" required>
        <button type="submit">Загрузить</button>
    </form>
</body>
</html>

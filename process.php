<?php
require 'vendor/autoload.php'; // Подключаем PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['excelFile']['name']);
    $filePath = $uploadDir . $fileName;

    // Проверяем и сохраняем файл
    if (move_uploaded_file($_FILES['excelFile']['tmp_name'], $filePath)) {
        echo "Файл успешно загружен: $fileName<br>";

        try {
            // Подключение к базе данных MySQL через PDO
            $pdo = new PDO('mysql:host=localhost;dbname=mapoil;charset=utf8', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Загружаем Excel-файл через PhpSpreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheet(0); // Первая страница (январь)

            $data = [];
            $maxColumn = 'H'; // Последний столбец — "Сдача с начала года (тонн)"

            // Пропускаем первые 5 строк и начинаем обработку данных
            for ($row = 6; $row <= $sheet->getHighestRow(); $row++) {
                $recordNumber = $sheet->getCell('A' . $row)->getValue();
                
                // Проверяем, что "№ п/п" начинается с "2." и не превышает "2.7.6"
                if (preg_match('/^2\./', $recordNumber) && $recordNumber !== '2.7.6') {
                    $data[] = [
                        'record_number' => $recordNumber,
                        'name' => $sheet->getCell('B' . $row)->getValue(),
                        'contract_info' => $sheet->getCell('C' . $row)->getValue(),
                        'gross_quantity' => (float)$sheet->getCell('D' . $row)->getValue(),
                        'net_quantity' => (float)$sheet->getCell('E' . $row)->getValue(),
                        'mouth_production' => (float)$sheet->getCell('F' . $row)->getValue(),
                        'year_to_date_production' => (float)$sheet->getCell('G' . $row)->getValue(),
                        'year_to_date_delivery' => (float)$sheet->getCell('H' . $row)->getValue()
                    ];
                }
            }

            // Вставляем данные в таблицу MaterialReport
            $stmt = $pdo->prepare("INSERT INTO MaterialReport (record_number, name, contract_info, gross_quantity, net_quantity, mouth_production, year_to_date_production, year_to_date_delivery) VALUES (:record_number, :name, :contract_info, :gross_quantity, :net_quantity, :mouth_production, :year_to_date_production, :year_to_date_delivery)");

            foreach ($data as $row) {
                $stmt->execute($row);
            }

            echo "Импорт данных успешно выполнен.";

        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage();
        }
    } else {
        echo "Ошибка при загрузке файла.";
    }
}

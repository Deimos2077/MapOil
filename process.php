<?php
require 'vendor/autoload.php'; // Подключаем PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['excelFile']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['excelFile']['tmp_name'], $filePath)) {
        echo "Файл успешно загружен: $fileName<br>";

        try {
            $pdo = new PDO('mysql:host=localhost;dbname=mapoil;charset=utf8', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheet(0); // Первая страница (январь)

            $reservoirData = [];

            for ($row = 6; $row <= $sheet->getHighestRow(); $row++) {
                $recordNumber = $sheet->getCell('A' . $row)->getValue(); // № п/п
                $volume = (float)$sheet->getCell('D' . $row)->getCalculatedValue(); // Объем
                $name = $sheet->getCell('B' . $row)->getValue(); // Описание строки

                preg_match('/\d{2}\.\d{2}\.\d{4}/', $name, $matches);
                $date = isset($matches[0]) ? date('Y-m-d', strtotime($matches[0])) : null;

                if ($recordNumber && $date) {
                    $reservoir_id = null;
                    $isStartVolume = false;

                    // Определяем reservoir_id и тип объема по номеру записи
                    switch ($recordNumber) {
                        case '2.1.1':
                            $reservoir_id = 1;
                            $isStartVolume = true;
                            break;
                        case '2.1.7':
                            $reservoir_id = 1;
                            $isStartVolume = false;
                            break;
                        // Добавьте другие номера для других резервуаров...
                    }

                    if ($reservoir_id !== null) {
                        if (!isset($reservoirData[$reservoir_id])) {
                            $reservoirData[$reservoir_id] = [
                                'date' => $date,
                                'start_volume' => 0,
                                'end_volume' => 0
                            ];
                        }

                        if ($isStartVolume) {
                            $reservoirData[$reservoir_id]['start_volume'] = $volume;
                        } else {
                            $reservoirData[$reservoir_id]['end_volume'] = $volume;
                        }
                    }
                }
            }

            // Вставляем данные в таблицу reservoirvolumes
            $stmt = $pdo->prepare("INSERT INTO reservoirvolumes (reservoir_id, date, start_volume, end_volume) VALUES (:reservoir_id, :date, :start_volume, :end_volume)");

            foreach ($reservoirData as $reservoir_id => $data) {
                $stmt->execute([
                    ':reservoir_id' => $reservoir_id,
                    ':date' => $data['date'],
                    ':start_volume' => $data['start_volume'],
                    ':end_volume' => $data['end_volume']
                ]);
            }

            echo "<script>
                alert('Данные для резервуаров успешно импортированы.');
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 5000);
            </script>";

        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage();
        }
    }
    header('Location: table.php');
    exit();
}

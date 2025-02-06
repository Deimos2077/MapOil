<?php
include('database/db.php'); // Подключаем базу данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем все числовые значения из формы
    $grosses = $_POST['gross'];
    $nets = $_POST['net'];
    $januarys = $_POST['january'];
    $yearlies = $_POST['yearly'];
    $deliveries = $_POST['delivery'];

    try {
        $pdo->beginTransaction(); // Начинаем транзакцию

        foreach ($grosses as $key => $gross) {
            // Для каждой строки берем значение по ключу (например, 1.1, 2.1.1)
            $net = $nets[$key];
            $january = $januarys[$key];
            $yearly = $yearlies[$key];
            $delivery = $deliveries[$key];

            // Определяем, в какую таблицу сохранять данные
            if (strpos($key, '1.') === 0) {
                // Раздел "СДАЧА НЕФТИ" — таблица oiltransfer
                $query = "INSERT INTO oiltransfer (row_key, gross, net, january, yearly, delivery)
                          VALUES (:row_key, :gross, :net, :january, :yearly, :delivery)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'row_key' => $key,
                    'gross' => $gross,
                    'net' => $net,
                    'january' => $january,
                    'yearly' => $yearly,
                    'delivery' => $delivery
                ]);
            } elseif (strpos($key, '2.') === 0) {
                // Раздел "ТРАНСПОРТИРОВКА, ПОТЕРИ, ОСТАТКИ НЕФТИ" — таблица reservoirvolumes
                $query = "INSERT INTO reservoirvolumes (row_key, gross, net, january, yearly, delivery)
                          VALUES (:row_key, :gross, :net, :january, :yearly, :delivery)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'row_key' => $key,
                    'gross' => $gross,
                    'net' => $net,
                    'january' => $january,
                    'yearly' => $yearly,
                    'delivery' => $delivery
                ]);
            }
        }

        $pdo->commit(); // Фиксируем транзакцию
        echo "Данные успешно сохранены!";
    } catch (Exception $e) {
        $pdo->rollBack(); // Откатываем изменения в случае ошибки
        echo "Ошибка при сохранении данных: " . $e->getMessage();
    }
}
?>

<?php
require_once 'database/db.php';

// Получаем дату из формы
$date = $_GET['date'] ?? null;

// Инициализируем сумму
$sum = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE (from_point_id = 11 OR from_point_id = 12)
          AND to_point_id = 5
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum = $row['total_sum'] ?? 0;
}
// По умолчанию нули
$start_volume_1 = $end_volume_1 = 0;
$start_volume_2 = $end_volume_2 = 0;

if ($date) {
    // Резервуар 1
    $stmt1 = $pdo->prepare("
        SELECT start_volume, end_volume
        FROM reservoirvolumes
        WHERE reservoir_id = 1 AND date = :date
        LIMIT 1
    ");
    $stmt1->execute([':date' => $date]);
    $res1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    if ($res1) {
        $start_volume_1 = $res1['start_volume'];
        $end_volume_1 = $res1['end_volume'];
    }

    // Резервуар 2
    $stmt2 = $pdo->prepare("
        SELECT start_volume, end_volume
        FROM reservoirvolumes
        WHERE reservoir_id = 2 AND date = :date
        LIMIT 1
    ");
    $stmt2->execute([':date' => $date]);
    $res2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($res2) {
        $start_volume_2 = $res2['start_volume'];
        $end_volume_2 = $res2['end_volume'];
    }
}
?>
<html>
<head>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .indent1 {
            padding-left: 20px;
        }
        .indent2 {
            padding-left: 40px;
        }
        .italic {
            font-style: italic;
        }
    </style>

</head>
<body>
<form method="GET">
    <label style="display:block" for="date-input">Дата:</label>
    <input type="date" id="date-input" name="date" class="form-control mb-3">
    <button type="submit">Показать сумму</button>
</form>
    <table>
        <tr>
            <th class="center">№</th>
            <th>Наименование</th>
            <th>Кол-во<br>(тонн нетто)</th>
        </tr>
        <tr>
            <td class="center">1</td>
            <td><strong>СДАЧА НЕФТИ:</strong></td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">1.1</td>
            <td class="indent1">СДАЧА НЕФТИ в систему АО "КазТрансОйл"</td>
            <td class="right"><span id="sum"><?= number_format($sum, 2, '.', ' ') ?></span></td>
        </tr>
        <tr>
            <td class="center">1.2</td>
            <td class="indent1">СДАЧА НЕФТИ в ТОО "Актобе нефтепереработка" (самовывоз)</td>
            <td class="right"><?= number_format($sum, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2</td>
            <td><strong>ТРАНСПОРТИРОВКА, ПОТЕРИ, ОСТАТКИ НЕФТИ:</strong></td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.1</td>
            <td class="indent1">по Актюбинскому НУ-ЭО АО "КазТрансОйл"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.1.1</td>
            <td class="indent2 italic">Остатки на ПСП 45 км на состоянию на начало месяца</td>
            <td class="right"><?= number_format($start_volume_1, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.2</td>
            <td class="indent2 italic">Остатки на КПОУ Жанажол на состоянию на начало месяца</td>
            <td class="right"><?= number_format($start_volume_2, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.3</td>
            <td class="indent2">Сдача в нефтепровод ПСП 45 км - ГНПС Кенкияк</td>
            <td class="right">28 737</td>
        </tr>
        <tr>
            <td class="center">2.1.4</td>
            <td class="indent2">Сдача в нефтепровод КПОУ Жанажол - ГНПС Кенкияк</td>
            <td class="right">1 350</td>
        </tr>
        <tr>
            <td class="center">2.1.5</td>
            <td class="indent2">Принято в ГНПС Кенкияк</td>
            <td class="right">30 074</td>
        </tr>
        <tr>
            <td class="center">2.1.6</td>
            <td class="indent2">Передано в нефтепровод ГНПС Кенкияк - ГНПС Кумколь (ККТ)</td>
            <td class="right">26 066</td>
        </tr>
        <tr>
            <td class="center">2.1.7</td>
            <td class="indent2">Кенкияк - НПС им. Шманова (Мартыши)</td>
            <td class="right">4 008</td>
        </tr>
        <tr>
            <td class="center">2.1.8</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">13</td>
        </tr>
        <tr>
            <td class="center">2.1.9</td>
            <td class="indent2 italic">Остатки на ПСП 45 км на состоянию на конец месяца</td>
            <td class="right"><?= number_format($end_volume_1, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.10</td>
            <td class="indent2 italic">Остатки на КПОУ Жанажол на состоянию на конец месяца</td>
            <td class="right"><?= number_format($end_volume_2, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2</td>
            <td class="indent1">по системе ТОО "Казахстанско-Китайский Трубопровод"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.2.1</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на 01.01.2025 г.</td>
            <td class="right">11 596</td>
        </tr>
        <tr>
            <td class="center">2.2.2</td>
            <td class="indent2">Принято в нефтепровод ГНПС Кенкияк - ГНПС Кумколь</td>
            <td class="right">26 066</td>
        </tr>
        <tr>
            <td class="center">2.2.3</td>
            <td class="indent2">Передано в ГНПС Кумколь (для ПКОП)</td>
            <td class="right">21 038</td>
        </tr>
        <tr>
            <td class="center">2.2.4</td>
            <td class="indent2">Передано в ГНПС Кумколь (для ГНПС Атасу)</td>
            <td class="right">5 007</td>
        </tr>
        <tr>
            <td class="center">2.2.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">21</td>
        </tr>
        <tr>
            <td class="center">2.2.6</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на 31.01.2025 г.</td>
            <td class="right">11 596</td>
        </tr>
        <tr>
            <td class="center">2.3</td>
            <td class="indent1">по Восточному филиалу АО "КазТрансОйл"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.3.1</td>
            <td class="indent2 italic">Остатки на ГНПС Кумколь на состоянию на 01.01.2025 г.</td>
            <td class="right">1 213</td>
        </tr>
        <tr>
            <td class="center">2.3.2</td>
            <td class="indent2">Принято в ГНПС Кумколь</td>
            <td class="right">26 045</td>
        </tr>
        <tr>
            <td class="center">2.3.3</td>
            <td class="indent2">Передано в ПКОП</td>
            <td class="right">21 000</td>
        </tr>
        <tr>
            <td class="center">2.3.4</td>
            <td class="indent2">Передано в ГНПС Атасу</td>
            <td class="right">5 000</td>
        </tr>
        <tr>
            <td class="center">2.3.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">45</td>
        </tr>
        <tr>
            <td class="center">2.3.7</td>
            <td class="indent2 italic">Остатки на ГНПС Кумколь на состоянию на 31.01.2025 г.</td>
            <td class="right">1 213</td>
        </tr>
        <tr>
            <td class="center">2.4</td>
            <td class="indent1">по системе ТОО "Казахстанско-Китайский Трубопровод"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.4.1</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на 01.01.2025 г.</td>
            <td class="right">0</td>
        </tr>
        <tr>
            <td class="center">2.4.2</td>
            <td class="indent2">Принято в ГНПС Атасу</td>
            <td class="right">5 000</td>
        </tr>
        <tr>
            <td class="center">2.4.3</td>
            <td class="indent2">Передано в Алашанькоу</td>
            <td class="right">4 995</td>
        </tr>
        <tr>
            <td class="center">2.4.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">5</td>
        </tr>
        <tr>
            <td class="center">2.4.6</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на 31.01.2025 г.</td>
            <td class="right">0</td>
        </tr>
        <tr>
            <td class="center">2.5</td>
            <td class="indent1">по системе ТОО "СЗК Мунайтас"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.5.1</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на 01.01.2025 г.</td>
            <td class="right">1 022</td>
        </tr>
        <tr>
            <td class="center">2.5.2</td>
            <td class="indent2">Принято в нефтепровод ГНПС Кенкияк - НПС им. Шманова</td>
            <td class="right">4 008</td>
        </tr>
        <tr>
            <td class="center">2.5.3</td>
            <td class="indent2">Передано в нефтепровод НПС им. Шманова - НПС им. Касымова</td>
            <td class="right">4 006</td>
        </tr>
        <tr>
            <td class="center">2.5.4</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">2</td>
        </tr>
        <tr>
            <td class="center">2.5.5</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на 31.01.2025 г.</td>
            <td class="right">1 022</td>
        </tr>
        <tr>
            <td class="center">2.6</td>
            <td class="indent1">по Западному филиалу АО "КазТрансОйл"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.6.1</td>
            <td class="indent2 italic">Остатки на НПС им. Шманева на состоянию на 01.01.2025 г.</td>
            <td class="right">440</td>
        </tr>
        <tr>
            <td class="center">2.6.2</td>
            <td class="indent2">Принято НПС им. Шманова</td>
            <td class="right">4 006</td>
        </tr>
        <tr>
            <td class="center">2.6.3</td>
            <td class="indent2">Сдано в систему КТК</td>
            <td class="right">4 000</td>
        </tr>
        <tr>
            <td class="center">2.6.4</td>
            <td class="indent2">Передано 12353 км (граница РК-РФ)</td>
            <td class="right">0</td>
        </tr>
        <tr>
            <td class="center">2.6.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">6</td>
        </tr>
        <tr>
            <td class="center">2.6.6</td>
            <td class="indent2 italic">Остатки на НПС им. Шманева на состоянию на 31.01.2025 г.</td>
            <td class="right">440</td>
        </tr>
        <tr>
            <td class="center">2.7</td>
            <td class="indent1">по сист ему АО "КазТрансОйл"/ ПАО "Транснефть"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.7.1</td>
            <td class="indent2 italic">Остатки на системе ПАО "Транснефть" на состоянию на 01.01.2025 г.</td>
            <td class="right">0</td>
        </tr>
        <tr>
            <td class="center">2.7.2</td>
            <td class="indent2">Принято в ПСН Самара (граница РК-РФ)</td>
            <td class="right">4 000</td>
        </tr>
        <tr>
            <td class="center">2.7.3</td>
            <td class="indent2">Передано в порт Усть-Луга</td>
            <td class="right">3 995.132</td>
        </tr>
        <tr>
            <td class="center">2.7.4</td>
            <td class="indent2">Передано в Новороссийск</td>
            <td class="right">0</td>
        </tr>
        <tr>
            <td class="center">2.7.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right">4.868</td>
        </tr>
        <tr>
            <td class="center">2.7.6</td>
            <td class="indent2 italic">Остатки на системе ПАО "Транснефть" на состоянию на 31.01.2025 г.</td>
            <td class="right">0</td>
        </tr>
    </table>

</body>

</html>
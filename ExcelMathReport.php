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

$sum2 = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE (from_point_id = 11 OR from_point_id = 12)
          AND to_point_id = 5
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum2 = $row['total_sum'] ?? 0;
}

$sumPkop = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) + losses AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 4
          AND to_point_id = 6
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumPkop = $row['total_sum'] ?? 0;
}

$Pkop = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 4
          AND to_point_id = 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $Pkop = $row['total_sum'] ?? 0;
}

$PNHZ = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 2
          AND to_point_id = 3
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $PNHZ = $row['total_sum'] ?? 0;
}

$sumAtasu = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 2
          AND to_point_id = 1
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumAtasu = $row['total_sum'] ?? 0;
}
$Alashankou = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 2
          AND to_point_id = 1
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $Alashankou = $row['total_sum'] ?? 0;
}

$sumKumkol = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 5
          AND to_point_id = 4
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumKumkol = $row['total_sum'] ?? 0;
}

$sumKumkol2 = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 5
          AND to_point_id = 4
          AND piplines_system_id BETWEEN 1 AND 6
          AND piplines_system_id != 4
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumKumkol2 = $row['total_sum'] ?? 0;
}


$sumShmanova = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 5
          AND to_point_id = 7
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumShmanova = $row['total_sum'] ?? 0;
}

$sumKasimova = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 7
          AND to_point_id = 19
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumKasimova = $row['total_sum'] ?? 0;
}
$sum12353km = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 19
          AND to_point_id = 24
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum12353km = $row['total_sum'] ?? 0;
}

$nova = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE to_point_id = 9
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nova = $row['total_sum'] ?? 0;
}

$ustLuga = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE to_point_id = 10
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $ustLuga = $row['total_sum'] ?? 0;
}

$sumDzhumagalieva = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 4
          AND to_point_id = 14
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumDzhumagalieva = $row['total_sum'] ?? 0;
}

$sum_without_12 = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 12
          AND to_point_id = 5
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum_without_12 = $row['total_sum'] ?? 0;
}
$sum_without_11 = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(from_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 11
          AND to_point_id = 5
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum_without_11 = $row['total_sum'] ?? 0;
}
$sum_losses = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(losses) AS total_losses
        FROM oiltransfer
        WHERE (from_point_id = 11 OR from_point_id = 12)
          AND to_point_id = 5
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum_losses = $row['total_losses'] ?? 0;
}
// $sum_losses2 = 0;

// if ($date) {
//     $stmt = $pdo->prepare("
//         SELECT SUM(losses) AS total_losses
//         FROM oiltransfer
//         WHERE from_point_id = 5
//           AND to_point_id = 4
//           AND piplines_system_id BETWEEN 1 AND 6
//           AND date = :date
//     ");
//     $stmt->execute([':date' => $date]);
//     $row = $stmt->fetch(PDO::FETCH_ASSOC);
//     $sum_losses2 = $row['total_losses'] ?? 0;
// }
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
    // Резервуар 3
    $stmt3 = $pdo->prepare("
        SELECT start_volume, end_volume
        FROM reservoirvolumes
        WHERE reservoir_id = 3 AND date = :date
        LIMIT 1
    ");
    $stmt3->execute([':date' => $date]);
    $res3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    if ($res2) {
        $start_volume_3 = $res2['start_volume'];
        $end_volume_3 = $res2['end_volume'];
    }
    // Резервуар 4
    $stmt4 = $pdo->prepare("
        SELECT start_volume, end_volume
        FROM reservoirvolumes
        WHERE reservoir_id = 4 AND date = :date
        LIMIT 1
    ");
    $stmt4->execute([':date' => $date]);
    $res4 = $stmt4->fetch(PDO::FETCH_ASSOC);
    if ($res4) {
        $start_volume_4 = $res4['start_volume'];
        $end_volume_4 = $res4['end_volume'];
    }
    // Резервуар 5
    $stmt5 = $pdo->prepare("
        SELECT start_volume, end_volume
        FROM reservoirvolumes
        WHERE reservoir_id = 5 AND date = :date
        LIMIT 1
    ");
    $stmt5->execute([':date' => $date]);
    $res5 = $stmt5->fetch(PDO::FETCH_ASSOC);
    if ($res5) {
        $start_volume_5 = $res5['start_volume'];
        $end_volume_5 = $res5['end_volume'];
    }
    // Резервуар 6
    $stmt6 = $pdo->prepare("
        SELECT start_volume, end_volume
        FROM reservoirvolumes
        WHERE reservoir_id = 6 AND date = :date
        LIMIT 1
    ");
    $stmt6->execute([':date' => $date]);
    $res6 = $stmt6->fetch(PDO::FETCH_ASSOC);
    if ($res6) {
        $start_volume_6 = $res6['start_volume'];
        $end_volume_6 = $res6['end_volume'];
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
            <td class="right"><?= number_format($sum_without_12, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.4</td>
            <td class="indent2">Сдача в нефтепровод КПОУ Жанажол - ГНПС Кенкияк</td>
            <td class="right"><?= number_format($sum_without_11, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.5</td>
            <td class="indent2">Принято в ГНПС Кенкияк</td>
            <td class="right"><?= number_format($sum2, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.6</td>
            <td class="indent2">Передано в нефтепровод ГНПС Кенкияк - ГНПС Кумколь (ККТ)</td>
            <td class="right"><?= number_format($sumKumkol, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.7</td>
            <td class="indent2">Кенкияк - НПС им. Шманова (Мартыши)</td>
            <td class="right"><?= number_format($sumShmanova, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.1.8</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sum_losses, 2, '.', ' ') ?></td>
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
            <td class="indent2 italic">Остатки технологической нефти на состоянию на начало месяца </td>
            <td class="right">11 596</td>
        </tr>
        <tr>
            <td class="center">2.2.2</td>
            <td class="indent2">Принято в нефтепровод ГНПС Кенкияк - ГНПС Кумколь</td>
            <td class="right"><?= number_format($sumKumkol2, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.3</td>
            <td class="indent2">Передано в ГНПС Кумколь (для ПКОП)</td>
            <td class="right"><?= number_format($sumPkop, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.4</td>
            <td class="indent2">Передано в ГНПС Кумколь (для ГНПС Атасу)</td>
            <td class="right"><?= number_format($sumDzhumagalieva, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($techLosses = $sumKumkol2 - ($sumPkop + $sumDzhumagalieva), 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.6</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на конец месяца.</td>
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
            <td class="right"><?= number_format($lossesKum = $sumPkop + $sumDzhumagalieva, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3.3</td>
            <td class="indent2">Передано в ПКОП</td>
            <td class="right"><?= number_format($Pkop, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3.4</td>
            <td class="indent2">Передано в ПНХЗ</td>
            <td class="right"><?= number_format($PNHZ, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3.5</td>
            <td class="indent2">Передано в ГНПС Атасу</td>
            <td class="right"><?= number_format($sumAtasu, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3.6</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($lossesKum-($PNHZ+$Pkop+$sumAtasu), 2, '.', ' ') ?></td>
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
            <td class="right"><?= number_format($sumAtasu, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.4.3</td>
            <td class="indent2">Передано в Алашанькоу</td>
            <td class="right"><?= number_format($Alashankou, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.4.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sumAtasu-$Alashankou, 2, '.', ' ') ?></td>
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
            <td class="right"><?= number_format($sumShmanova,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.5.3</td>
            <td class="indent2">Передано в нефтепровод НПС им. Шманова - НПС им. Касымова</td>
            <td class="right"><?= number_format($sumKasimova,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.5.4</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sumShmanova-$sumKasimova,2,'.',' ')  ?></td>
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
            <td class="right"><?= number_format($sumKasimova,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.6.3</td>
            <td class="indent2">Передано 12353 км (граница РК-РФ)</td>
            <td class="right"><?= number_format($sum12353km,2,'.',' ')  ?></td>
        </tr>

        <tr>
            <td class="center">2.6.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sumKasimova-$sum12353km,2,'.',' ')  ?></td>
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
            <td class="right"><?= number_format($sum12353km,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.7.3</td>
            <td class="indent2">Передано в порт Усть-Луга</td>
            <td class="right"><?= number_format($ustLuga,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.7.4</td>
            <td class="indent2">Передано в Новороссийск</td>
            <td class="right"><?= number_format($nova,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.7.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sum12353km-($nova+$ustLuga),2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.7.6</td>
            <td class="indent2 italic">Остатки на системе ПАО "Транснефть" на состоянию на 31.01.2025 г.</td>
            <td class="right">0</td>
        </tr>
    </table>

</body>

</html>
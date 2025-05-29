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
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 5
          AND to_point_id = 4
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

$Pkopfrom = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 5
          AND piplines_system_id IN (1, 4)
          AND to_point_id = 4
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $Pkopfrom = $row['total_sum'] ?? 0;
}

$sumLossesUst = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(losses) AS total_losses
        FROM oiltransfer
        WHERE date = :date
          AND (
                (from_point_id = 7 AND to_point_id = 19)
             OR (from_point_id = 19 AND to_point_id = 24)
          )
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumLossesUst = $row['total_losses'] ?? 0;
}

$lossesKasimov = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(losses) AS total_losses
        FROM oiltransfer
        WHERE date = :date
          AND (
                (from_point_id = 7 AND to_point_id = 19)
          )
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $lossesKasimov = $row['total_losses'] ?? 0;
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
        SELECT SUM(to_amount) AS total_sum
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

// $minusVolumeKumkol = 0;

// if ($date) {
//     $stmt = $pdo->prepare("
//         SELECT minus_volume 
//         FROM reservoirvolumes
//         WHERE reservoir_id = 3
//           AND date = :date
//     ");
//     $stmt->execute([':date' => $date]);
//     $row = $stmt->fetch(PDO::FETCH_ASSOC);
//     $minusVolumeKumkol = $row['minus_volume'] ?? 0;
// }

// $plusVolumeKumkol = 0;

// if ($date) {
//     $stmt = $pdo->prepare("
//         SELECT plus_volume  
//         FROM reservoirvolumes
//         WHERE reservoir_id = 3
//           AND date = :date
//     ");
//     $stmt->execute([':date' => $date]);
//     $row = $stmt->fetch(PDO::FETCH_ASSOC);
//     $plusVolumeKumkol = $row['plus_volume'] ?? 0;
// }

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
$sumKasimova2 = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(to_amount) AS total_sum
        FROM oiltransfer
        WHERE from_point_id = 7
          AND to_point_id = 19
          AND piplines_system_id BETWEEN 1 AND 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sumKasimova2 = $row['total_sum'] ?? 0;
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
$sum_losses2 = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(losses) AS total_losses
        FROM oiltransfer
        WHERE from_point_id = 5
          AND to_point_id = 4
          AND piplines_system_id BETWEEN 1 AND 2
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sum_losses2 = $row['total_losses'] ?? 0;
}

$lossesPkop = 0;

if ($date) {
    $stmt = $pdo->prepare("
        SELECT SUM(losses) AS total_losses
        FROM oiltransfer
        WHERE from_point_id = 4
          AND to_point_id = 6
          AND date = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $lossesPkop = $row['total_losses'] ?? 0;
}
// По умолчанию нули
$start_volume_1 = $end_volume_1 = 0;
$start_volume_2 = $end_volume_2 = 0;
$start_volume_3 = $end_volume_3 = 0;
$start_volume_4 = $end_volume_4 = 0;
$start_volume_5 = $end_volume_5 = 0;
$start_volume_6 = $end_volume_6 = 0;
if ($date) {
    // Резервуар 1
    $start_volume_1 = $start_volume_1 ?? 0;
    $end_volume_1 = $end_volume_1 ?? 0;
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
            text-align: center;
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
    <style>
        .main-input {
            background-color: #ffcccc !important;
            border: 2px solid red;
            font-weight: bold;
        }
        #date-input{
            width: 17%;
        }
        .gray-input{
            background-color:rgb(218, 217, 217) !important;
            font-weight: bold;
        }
        .highlight-date {
        background: #ffcc00 !important; /* Жёлтая подсветка */
        color: black !important;
        border-radius: 50%;
    }
    .highlight-last-day {
        background: #ff4d4d !important; /* Красная подсветка */
        color: white !important;
        border-radius: 50%;
    }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/modal_set.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
                    <!-- Модальное окно -->
                    <div id="settings-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Настройки</h2>
                    <ul>
                        <li>
                            <span data-i18n="settings_language">Язык интерфейса:</span>
                            <select id="language-select">
                                <option value="ru">Русский</option>
                                <option value="en">English</option>
                                <option value="zh">中文</option>
                            </select>
                        </li>
                        <li>
                            <span data-i18n="settings_font_size">Размер шрифта:</span>
                            <input type="range" id="font-size" min="12" max="24" step="1">
                        </li>
                        <li><a href="#" id="export-excel" data-i18n="settings_export_excel">Экспорт данных в Excel</a></li>
                        <li><a href="#" id="export-pdf" data-i18n="settings_export_pdf">Экспорт данных в PDF</a></li>
                        <li>
                            <span data-i18n="settings_email_report">Отчет по email:</span>
                            <input type="email" id="email" placeholder="Введите email">
                            <button id="send-report">Отправить</button>
                        </li>
                        <li><a href="#" id="help-button" data-i18n="settings_help">Помощь</a></li>
                        <li><a href="/project/MapOil/password_change.php" data-i18n="settings_password_change">Смена пароля</a></li>
                        <li><a href="/project/MapOil/login_history.php" data-i18n="settings_login_history">История входов</a></li>
                    </ul>
                </div>
            </div>
<nav id="slide-menu">
    <ul>
        <li class="map"><a class="menu-href" href="/project/MapOil/map.php" ><i class="fa fa-map" ></i>Карта транспортировки нефти</a></li>        
        <li class="calculator"><a class="menu-href" href="/project/MapOil/calculator.php" data-i18n="menu_calculator"><i class="fa fa-file-text"></i>Отчетность</a></li>
        <li class="timeline"><a class="menu-href" href="/project/Graph/analysis.php" data-i18n="menu_graphs">Графики</a></li>
        <li class="report"><a class="menu-href" href="/project/MapOil/ExcelMathReport.php" ><i class="fa fa-file"></i>МатОтчет</a></li>
        <!-- <li class="svg-editor">
            <a class="menu-href" href="/project/svgedit-master/dist/editor/" target="_blank">Редактор SVG</a>
        </li> -->
        <!-- <li class="settings"><a href="#" id="settings-toggle" data-i18n="menu_settings">Настройки</a></li> -->
        <li class="logout"><a href="logout.php" data-i18n="menu_logout">Выход</a></li>
    </ul>
</nav>
<div id="content">
<div class="menu-trigger"></div>
<form method="GET">
    <label style="display:block" for="date-input">Дата:</label>
    <input type="date" id="date-input" name="date" class="form-control mb-3">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary mb-4" type="submit">Показать сумму</button>
        <button class="btn btn-primary mb-4" onclick="exportToExcel()">Экспорт в Excel</button>
    </div>
</form>
    <table id="myTable" class="table table-bordered table-striped">
        <tr>
            <th class="center">№</th>
            <th>Наименование</th>
            <th>Кол-во<br>(тонн нетто)</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="center">1</td>
            <td><strong>СДАЧА НЕФТИ:</strong></td>
            <td class="right"><?= number_format($sum, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">1.1</td>
            <td class="indent1">СДАЧА НЕФТИ в систему АО "КазТрансОйл"</td>
            <td class="right"><span id="sum"><?= number_format($sum, 2, '.', ' ') ?></span></td>
        </tr>
        <tr>
            <td class="center">1.2</td>
            <td class="indent1">СДАЧА НЕФТИ в ТОО "Актобе нефтепереработка" (самовывоз)</td>
            <td class="right"></td>
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
            <td class="right"></td>
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
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.2</td>
            <td class="indent1">по системе ТОО "Казахстанско-Китайский Трубопровод"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.2.1</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на начало месяца </td>
            <td class="right"><?= number_format($start_volume_5, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.2</td>
            <td class="indent2">Принято в нефтепровод ГНПС Кенкияк - ГНПС Кумколь</td>
            <td class="right"><?= number_format($sumKumkol, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.3</td>
            <td class="indent2">Передано в ГНПС Кумколь (для ПКОП)</td>
            <td class="right"><?= number_format($Pkopfrom, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.4</td>
            <td class="indent2">Передано в ГНПС Кумколь (для ГНПС Атасу)</td>
            <td class="right"><?= number_format($sumDzhumagalieva, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sum_losses2, 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.2.6</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на конец месяца.</td>
            <td class="right"><?= number_format($end_volume_5, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3</td>
            <td class="indent1">по Восточному филиалу АО "КазТрансОйл"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.3.1</td>
            <td class="indent2 italic">Остатки на ГНПС Кумколь на состоянию на начало месяца</td>
            <td class="right"><?= number_format($start_volume_3, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3.2</td>
            <td class="indent2">Принято в ГНПС Кумколь</td>
            <td class="right"><?= number_format($sumPkop, 2, '.', ' ') ?></td>
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
            <td class="right"><?= number_format($lossesPkop+($sumDzhumagalieva-$sumAtasu), 2, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.3.7</td>
            <td class="indent2 italic">Остатки на ГНПС Кумколь на состоянию на конец месяца</td>
            <td class="right"><?= number_format($end_volume_3, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.4</td>
            <td class="indent1">по системе ТОО "Казахстанско-Китайский Трубопровод"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.4.1</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на начало месяца</td>
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
            <td class="indent2 italic">Остатки технологической нефти на состоянию на конец месяца</td>
            <td class="right">0</td>
        </tr>
        <tr>
            <td class="center">2.5</td>
            <td class="indent1">по системе ТОО "СЗК Мунайтас"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.5.1</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на начало месяца</td>
            <td class="right"><?= number_format($start_volume_6, 0, '.', ' ') ?></td>
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
            <td class="right"><?= number_format($lossesKasimov,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.5.5</td>
            <td class="indent2 italic">Остатки технологической нефти на состоянию на конец месяца</td>
            <td class="right"><?= number_format($end_volume_6, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.6</td>
            <td class="indent1">по Западному филиалу АО "КазТрансОйл"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.6.1</td>
            <td class="indent2 italic">Остатки на НПС им. Шманева на состоянию на начало месяца</td>
            <td class="right"><?= number_format($start_volume_2, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.6.2</td>
            <td class="indent2">Принято НПС им. Шманова</td>
            <td class="right"><?= number_format($sumKasimova,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.6.3</td>
            <td class="indent2">Принято в НПС им. Касымова</td>
            <td class="right"><?= number_format($sumKasimova2,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.6.4</td>
            <td class="indent2">Передано 12353 км (граница РК-РФ)</td>
            <td class="right"><?= number_format($sum12353km,2,'.',' ')  ?></td>
        </tr>

        <tr>
            <td class="center">2.6.5</td>
            <td class="indent2">Технологические потери</td>
            <td class="right"><?= number_format($sumLossesUst,2,'.',' ')  ?></td>
        </tr>
        <tr>
            <td class="center">2.6.6</td>
            <td class="indent2 italic">Остатки на НПС им. Шманева на состоянию на конец месяца</td>
            <td class="right"><?= number_format($end_volume_2, 0, '.', ' ') ?></td>
        </tr>
        <tr>
            <td class="center">2.7</td>
            <td class="indent1">по сист ему АО "КазТрансОйл"/ ПАО "Транснефть"</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td class="center">2.7.1</td>
            <td class="indent2 italic">Остатки на системе ПАО "Транснефть" на состоянию на начало месяца</td>
            <td class="right"><?= number_format($start_volume_4, 0, '.', ' ') ?></td>
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
            <td class="indent2 italic">Остатки на системе ПАО "Транснефть" на состоянию на конец месяца</td>
            <td class="right"><?= number_format($end_volume_4, 0, '.', ' ') ?></td>
        </tr>
    </table>
</div>

<script>
function exportToExcel() {
  const table = document.querySelector("table");
  const rows = table.querySelectorAll("tr");
  const data = [];

  rows.forEach(row => {
    const rowData = [];
    const cells = row.querySelectorAll("th, td");

    cells.forEach(cell => {
      const text = cell.textContent.trim();
      const number = text.replace(/\s/g, '').replace(',', '.');
      if (!isNaN(number) && number !== "") {
        rowData.push(parseFloat(number));
      } else {
        rowData.push(text);
      }
    });

    data.push(rowData);
  });

  const ws = XLSX.utils.aoa_to_sheet(data);
  const wb = XLSX.utils.book_new();

  // Обводка и стили для всех ячеек
  for (let R = 0; R < data.length; R++) {
    for (let C = 0; C < data[R].length; C++) {
      const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
      if (!ws[cellAddress]) ws[cellAddress] = { t: "s", v: "" };

      if (!ws[cellAddress].s) ws[cellAddress].s = {};

      ws[cellAddress].s.border = {
        top:    { style: "thin", color: { rgb: "000000" } },
        bottom: { style: "thin", color: { rgb: "000000" } },
        left:   { style: "thin", color: { rgb: "000000" } },
        right:  { style: "thin", color: { rgb: "000000" } }
      };
    }
  }
// Применяем шрифт Times New Roman и размер 10 ко всем ячейкам
for (let r = 0; r < rows.length; r++) {
    let row = rows[r];
    for (let c = 0; c < row.cells.length; c++) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) continue;

        // Применяем шрифт ко всем ячейкам
        ws[cellRef].s = {
            ...ws[cellRef].s,
            font: {
                name: "Times New Roman",
                sz: 11
            }
        };
    }
}
  // Центрирование в колонке C
  for (let R = 0; R < data.length; R++) {
    const cellAddress = XLSX.utils.encode_cell({ r: R, c: 2 });
    if (!ws[cellAddress]) continue;
    ws[cellAddress].s.alignment = {
      horizontal: "center",
      vertical: "center"
    };
  }

  // Первая строка — жирный и wrap
  for (let C = 0; C < data[0].length; C++) {
    const cellAddress = XLSX.utils.encode_cell({ r: 0, c: C });
    if (!ws[cellAddress]) continue;
    ws[cellAddress].s.font = { bold: true };
    ws[cellAddress].s.alignment = {
      wrapText: true,
      horizontal: "center",
      vertical: "center"
    };
  }

  // Голубой фон для строк
  const blueRows = [2, 3, 4, 5, 6, 17, 24, 32, 38, 44, 51];
  blueRows.forEach(rowIdx => {
    for (let C = 0; C < data[rowIdx].length; C++) {
      const cellAddress = XLSX.utils.encode_cell({ r: rowIdx, c: C });
      if (!ws[cellAddress]) continue;
      ws[cellAddress].s.fill = {
        fgColor: { rgb: "D9EAF7" }
      };
    }
  });

    // Жирным строки
    const boldRows = [2, 7, 8, 15, 16, 18, 23, 25, 31, 33, 37, 39, 43, 45, 50, 52, 57];
  boldRows.forEach(rowIdx => {
    for (let C = 0; C < data[rowIdx].length; C++) {
      const cellAddress = XLSX.utils.encode_cell({ r: rowIdx, c: C });
      if (!ws[cellAddress]) continue;

      if (!ws[cellAddress].s) ws[cellAddress].s = {};
      if (!ws[cellAddress].s.font) ws[cellAddress].s.font = {};
      ws[cellAddress].s.font.bold = true;
    }
  });
  // Ширина колонок
  ws["!cols"] = [
    { wch: 10 },     // Колонка A
    { wpx: 444 },    // Колонка B (444 пикселей)
    { wch: 8 }      // Колонка C
  ];

  XLSX.utils.book_append_sheet(wb, ws, "Отчет");
  XLSX.writeFile(wb, "report.xlsx");
}
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch("get_dates.php")
            .then(response => response.json())
            .then(result => {
                const allDates = result.allDates;
                const latestPerMonth = result.latestPerMonth;

                flatpickr("#date-input", {
                    dateFormat: "Y-m-d",
                    locale: "ru",
                    onDayCreate: function(dObj, dStr, fp, dayElem) {
                        const dateStr = dayElem.dateObj.toLocaleDateString("sv-SE");

                        if (allDates.includes(dateStr)) {
                            if (latestPerMonth.includes(dateStr)) {
                                dayElem.classList.add("highlight-last-day"); // красный
                            } 
                            else {
                                dayElem.classList.add("highlight-date"); // жёлтый
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Ошибка загрузки дат:", error));
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/language.js"></script>
    <script src="js/Settings.js"></script>
    <script src="js/menu.js"></script>
</body>

</html>
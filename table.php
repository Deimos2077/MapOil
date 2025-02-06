<?php
include('database/db.php'); 

// SQL запрос для получения данных
$query = "SELECT oiltransfer.id, pipelines.company, points_from.name AS from_point, points_to.name AS to_point, oiltransfer.date, oiltransfer.from_amount, oiltransfer.to_amount, oiltransfer.losses 
          FROM oiltransfer
          JOIN pipelines ON oiltransfer.pipeline_id = pipelines.id
          JOIN points AS points_from ON oiltransfer.from_point_id = points_from.id
          JOIN points AS points_to ON oiltransfer.to_point_id = points_to.id";

$stmt = $pdo->query($query);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Данные о перекачке нефти</title>
  <link rel="stylesheet" href="css/table.css">
  <link rel="stylesheet" href="css/menu.css">
</head>

<body>
  <nav id="slide-menu">
    <ul>
      <li class="timeline"><a class="menu-href" href="http://localhost/oilgraf/">Графики</a></li>
      <li class="events"><a class="menu-href" href="http://localhost/mapoil/table.php">МатОтчет</a></li>
      <li class="timeline"><a class="menu-href" href="http://localhost/mapoil/map.php">Карта</a></li>
      <li class="sep settings"><a href="">Settings</a></li>
      <li class="logout"><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
  <div id="content">
    <div class="menu-trigger"></div>
    <h1>Данные о перекачке нефти</h1>
    <table >
      <thead>
        <tr>
          <th rowSpan="3">№ п/п</th>
          <th rowSpan="2">Наименование</th>
          <th rowSpan="2">Номер и дата договора</th>
          <th colSpan="2">Кол-во</th>
          <th rowSpan="2">Добыча за январь (тонн)</th>
          <th rowSpan="2">Добыча с начала года (тонн)</th>
          <th rowSpan="2">Сдача с начала года (тонн)</th>
        </tr>
        <tr>
          <th>(тонн брутто)</th>
          <th>(тонн нетто)</th>
        </tr>
      </thead>
      <tbody>

      <tr>
        <td>1.1</td>
        <td>СДАЧА НЕФТИ в систему АО "КазТрансОйл"</td>
        <td><input type="number" name="gross[1.1]" value="30087"></td>
        <td><input type="number" name="net[1.1]" value="30087"></td>
        <td><input type="number" name="january[1.1]" value="30087"></td>
        <td><input type="number" name="yearly[1.1]" value="30087"></td>
        <td><input type="number" name="delivery[1.1]" value="30087"></td>
      </tr>

        <tr>
          <td>1.1</td>
          <td>СДАЧА НЕФТИ в систему АО "КазТрансОйл"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>1.2</td>
          <td>СДАЧА НЕФТИ в ТОО "Актобе нефтепереработка" (самовывоз)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2</td>
          <td>ТРАНСПОРТИРОВКА, ПОТЕРИ, ОСТАТКИ НЕФТИ:</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1</td>
          <td>по Актюбинскому НУ ЗФ АО "КазТрансОйл"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.1</td>
          <td>Остатки на ПСП 45 км по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.2</td>
          <td>Остатки на КПОУ Жанажол по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.3</td>
          <td>Сдача в нефтепровод ПСП 45 км - ГНПС Кенкияк</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.4</td>
          <td>Сдача в нефтепровод КПОУ Жанажол -ГНПС Кенкияк </td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.5</td>
          <td>Принято в ГНПС Кенкияк</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.6</td>
          <td>Передано в нефтепровод ГНПС Кенкияк -ГНПС Кумколь (ККТ)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.7</td>
          <td>Передано в нефтепровод ГНПС Кенкияк - НПС им. Шманова (МунайТас)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.8</td>
          <td>Технологические потери</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.9</td>
          <td>Остатки на ПСП 45 км по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.1.10</td>
          <td>Остатки на КПОУ Жанажол по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2</td>
          <td>по системе ТОО "Казахстанско-Китайский Трубопровод"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2.1</td>
          <td>Остатки технологической нефти по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2.2</td>
          <td>Принято в нефтепровод ГНПС Кенкияк -ГНПС Кумколь</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2.3</td>
          <td>Передано в ГНПС Кумколь (для ПКОП)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2.4</td>
          <td>Передано в ГНПС Кумколь (для ГНПС Атасу)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2.5</td>
          <td>Технологические потери</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.2.6</td>
          <td>Остатки технологической нефти по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3</td>
          <td>по Восточному филиалу  АО "КазТрансОйл"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.1</td>
          <td>Остатки на ГНПС Кумколь по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.2</td>
          <td>Принято в ГНПС Кумколь </td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.3</td>
          <td>Передано в ПКОП</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.4</td>
          <td>Передано в ПНХЗ</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.5</td>
          <td>Передано в ГНПС Атасу</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.6</td>
          <td>Технологические потери</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.3.7</td>
          <td>Остатки на ГНПС Кумколь по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.4</td>
          <td>по системе ТОО "Казахстанско-Китайский Трубопровод"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.4.1</td>
          <td>Остатки технологической нефти по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.4.2</td>
          <td>Принято в ГНПС Атасу</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.4.3</td>
          <td>Передано в Алашанькоу</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.4.5</td>
          <td>Технологические потери</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.4.6</td>
          <td>Остатки технологической нефти по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.5</td>
          <td>по системе ТОО "СЗТК  Мунайтас"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.5.1</td>
          <td>Остатки технологической нефти по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.5.2</td>
          <td>Принято в нефтепровод ГНПС Кенкияк - НПС им. Шманова</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.5.3</td>
          <td>Передано в нефтепровод НПС им. Шманова - НПС им. Касымова</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.5.4</td>
          <td>Технологические потери</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.5.5</td>
          <td>Остатки технологической нефти по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6</td>
          <td>по Западному филиалу АО "КазТрансОйл"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6.1</td>
          <td>Остатки на НПС им. Шманова по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6.2</td>
          <td>Принято в НПС им. Шманова</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6.3</td>
          <td>Принято в НПС им. Касымова</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6.4</td>
          <td>Передано 1235,3 км (граница РК/РФ)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6.5</td>
          <td>Остатки на НПС им. Шманова по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.6.6</td>
          <td>Остатки на НПС им. Шманова по состоянию на 31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7</td>
          <td>по системе АО "КазТрансОйл" - ПАО "Транснефть"</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7.1</td>
          <td>Остатки в системе ПАО "Транснефть" по состоянию на 01.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7.2</td>
          <td>Принято в ПСН Самара (граница РК/РФ)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7.3</td>
          <td>Передано в порт Усть-Луга</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7.4</td>
          <td>Передано в порт Новороссийск</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7.5</td>
          <td>Технологические потери</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>2.7.6</td>
          <td>Остатки в системе ПАО "Транснефть" по состоянию на  31.01.2025 г.</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
    <button type="submit">Сохранить данные</button>
  </div>

  <script src="js/menu.js"></script>
  <script src="js/table.js"></script>
</body>

</html>

<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .red-bg {
            background-color: #ffcccc;
        }
        .header-row {
            background-color: #e6e6e6;
        }
        .left-align {
            text-align: left;
        }
        .right-align {
            text-align: right;
        }
        .subheader {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<button onclick="exportToExcel()">Экспорт в Excel</button>
    <table id="myTable" >
    <tr>
        <td colspan="23" style="font-weight: bold; text-align: center;">Расчет потерь при транспортировке нефти</td>
    </tr>
    <tr>
        <td colspan="23"></td> <!-- Пустая строка -->
    </tr>
        <tr class="header-row">
            <th colspan="2">Нефтепровод</th>
            <th colspan="1">Нормативные технич. потерь</th>
            <th colspan="2">Остаток</th>
            <th colspan="3">Внутренний рынок (ПКОП)</th>
            <th colspan="3">КНР</th>
            <th colspan="3">Европа (Усть-Луга)</th>
            <th colspan="3">Отгст.хранение</th>
            <th colspan="3">Внутренний рынок (ПНХ3)</th>
            <th colspan="3">Европа (Новороссийск)</th>
        </tr>
        <tr class="header-row">
            <th>ОТ</th>
            <th>ДО</th>
            <th></th>
            <th>на начало месяца</th>
            <th>на конец месяца</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
            <th>от</th>
            <th>Потеря</th>
            <th>до</th>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе МН АО "КАЗТРАНСОЙЛ" (Западный Филиал)</td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>3</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>ГНПС ПСП45</td>
            <td >ГНПС Кинкияк</td>
            <td>0,0332%</td>
            <td></td>
            <td></td>
            <td>16984</td>
            <td>6</td>
            <td>16978</td>
            <td>5013</td>
            <td>2</td>
            <td>5011</td>
            <td>4009</td>
            <td>1</td>
            <td>4008</td>
            <td>5011</td>
            <td>0</td>
            <td>5008</td>
            <td>5013</td>
            <td>2</td>
            <td>5011</td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">КПОУ Жанажол</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0377%</td>
            <td></td>
            <td></td>
            <td>1600</td>
            <td>1</td>
            <td>1599</td>
            <td></td>
            <td>0</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td>0</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Каламкас (перевалка)</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0277%</td>
            <td></td>
            <td></td>
            <td>18777</td>
            <td>1</td>
            <td>18776</td>
            <td>5011</td>
            <td>0</td>
            <td>5011</td>
            <td>4008</td>
            <td>0</td>
            <td>4008</td>
            <td>5008</td>
            <td>0</td>
            <td>5007</td>
            <td>5011</td>
            <td>0</td>
            <td>5011</td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе МН ТОО «Казахстанско-Китайский трубопровод»</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Кумколь</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0794%</td>
            <td></td>
            <td></td>
            <td>18576</td>
            <td>15</td>
            <td>18561</td>
            <td>5011</td>
            <td>4</td>
            <td>5007</td>
            <td></td>
            <td></td>
            <td></td>
            <td>5007</td>
            <td></td>
            <td>5000</td>
            <td>5011</td>
            <td>4</td>
            <td>5007</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе МН АО "КАЗТРАНСОЙЛ" (Восточный Филиал)</td>
            <td></td>
            <td>9000</td>
            <td>18523</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Кумколь</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,1818%</td>
            <td></td>
            <td></td>
            <td>2477</td>
            <td></td>
            <td></td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Кумколь</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td></td>
            <td></td>
            <td></td>
            <td>21038</td>
            <td>38</td>
            <td class="red-bg">21000</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="red-bg">5000</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Кумколь</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0525%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>5007</td>
            <td>3</td>
            <td>5004</td>
            <td></td>
            <td></td>
            <td></td>
            <td>5007</td>
            <td>3</td>
            <td>5004</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС им. Б. Джумагалиева</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0754%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>5004</td>
            <td>4</td>
            <td>5000</td>
            <td></td>
            <td></td>
            <td></td>
            <td>5004</td>
            <td>4</td>
            <td>5000</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Атасу (перекачка в н/п Атасу-Алашанькоу)</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0051%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>5000</td>
            <td>0</td>
            <td>5000</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Атасу</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0843%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>5000</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе МН ТОО «Казахстанско-Китайский трубопровод»</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Атасу</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0965%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="red-bg">5000</td>
            <td>5</td>
            <td>4995</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе МН АО "СЗТК "Мунайтас"</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ГНПС Каламкас</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0429%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>4008</td>
            <td>2</td>
            <td>4006</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе МН АО "КАЗТРАНСОЙЛ" (Западный Филиал)</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">НПС им. Шманова</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0455%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>4006</td>
            <td>2</td>
            <td>4004</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">НПС им. Касымова</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,1126%</td>
            <td></td>
            <td></td>
            <td>4004</td>
            <td>2</td>
            <td class="red-bg">4002</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">По системе ПАО "Транснефть"</td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Граница РК/РФ (Б.Чернигов)</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0192%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>4000</td>
            <td>1</td>
            <td>3999</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">Самара ВСН (на Хорхӧ) (перекачка)</td>
            <td>0,0137%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>3999</td>
            <td>1</td>
            <td>3998</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Самара</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0000%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>3998</td>
            <td>0</td>
            <td>3998</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Клин</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0065%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>3998</td>
            <td>0</td>
            <td>3998</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Никольское</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0458%</td>
            <td></td>
            <td></td>
            <td>27608</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>3998</td>
            <td>2</td>
            <td>3996</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Узвар (на Адриаполь)</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0588%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>3996</td>
            <td>1</td>
            <td>3995</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">НБ Усть-Луга (перевалка)</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">Погрузка нефти в танкер в порту Усть-Луга (перевалка)</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Самара</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0098%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Краснодарнефть</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0132%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">915 км н/пр КТЛ</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0000%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Ромашковская</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0108%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">ПНБ Тихорецк (перекачка)</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align">Тихорецк</td>
            <td class="left-align">ГНПС Кинкияк</td>
            <td>0,0181%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">ПК Шесхарис промплощадка Грушовая (перекачка)</td>
            <td>0,0293%</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td>0</td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">ПК Шесхарис промплощадка Шесхарис (перекачка для налива в)</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="left-align" colspan="2">Порт Новороссийск (перевалка)</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <script>
function exportToExcel() {
    let table = document.getElementById("myTable");
    let wb = XLSX.utils.book_new();
    let ws = XLSX.utils.aoa_to_sheet([]);

    // Устанавливаем ширину колонок
    ws['!cols'] = [
        { wpx: 181 }, { wpx: 212 }, { wpx: 86 }, { wpx: 86 }, { wpx: 81 }
    ];

    let rows = table.rows;
    let data = [];
    let merges = [];
    let borderStyle = { // Чёрные границы (толщина 1px)
        top: { style: "thin", color: { rgb: "000000" } },
        bottom: { style: "thin", color: { rgb: "000000" } },
        left: { style: "thin", color: { rgb: "000000" } },
        right: { style: "thin", color: { rgb: "000000" } }
    };

    // Функция для добавления стиля к ячейке
    function setCellStyle(ws, r, c, color, borderStyle) {
        let cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) ws[cellRef] = { v: "" };

        // Применяем как цвет фона, так и границы
        ws[cellRef].s = {
            fill: { fgColor: { rgb: color } },
            border: borderStyle
        };
    }

    for (let r = 0; r < rows.length; r++) {
        let row = rows[r];
        let rowData = [];
        let colOffset = 0;

        for (let c = 0; c < row.cells.length; c++) {
            let cell = row.cells[c];
            let text = cell.innerText || cell.textContent;
            let cellStyle = { v: text, s: {} };

            // Добавляем обводку только с 3-й строки до 42-й и до колонки W (22-я колонка)
            if (r >= 2 && r <= 41 && c <= 22) {
                cellStyle.s.border = borderStyle;
            }

            // Обработка объединённых ячеек
            let colspan = cell.colSpan || 1;
            let rowspan = cell.rowSpan || 1;

            if (colspan > 1 || rowspan > 1) {
                merges.push({
                    s: { r, c: c + colOffset },
                    e: { r: r + rowspan - 1, c: c + colOffset + colspan - 1 }
                });
            }

            // В 4-й строке C (индекс 2) пустая
            if (r === 3 && c === 2) {
                rowData.push({ v: "" });
            }

            rowData[c + colOffset] = cellStyle;
            colOffset += colspan - 1;
        }

        data.push(rowData);
    }

    // Записываем данные в лист
    XLSX.utils.sheet_add_aoa(ws, data);

    // Применяем объединение ячеек
    ws["!merges"] = merges;

    // Голубой фон для строк 3-7, 11, 13, 19, 21, 23, 26 (до W)
    let blueRows = [2, 3, 4, 10, 12, 18, 20, 22, 25];
    for (let r of blueRows) {
        for (let c = 0; c <= 22; c++) {
            setCellStyle(ws, r, c, "DDEBF7", borderStyle); // Голубой фон и чёрные границы
        }
    }

    // Голубой фон для колонок D,E (индексы 3,4) от 8 до 42 строки
    for (let r = 5; r <= 41; r++) {
        for (let c of [3, 4]) {
            setCellStyle(ws, r, c, "DDEBF7", borderStyle); // Голубой фон и чёрные границы
        }
    }

    // Коричневый фон для колонок F,G,H (индексы 5,6,7) от 8-10, 12, 14 строки
    let brownRows = [7, 8, 9, 11, 13];
    for (let r of brownRows) {
        for (let c of [5, 6, 7]) {
            setCellStyle(ws, r, c, "FCE4D6", borderStyle); // Коричневый фон и чёрные границы
        }
    }

    // Добавляем лист в книгу и сохраняем
    XLSX.utils.book_append_sheet(wb, ws, "Таблица");
    XLSX.writeFile(wb, "table.xlsx");
}
</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.min.js"></script>





</body>
</html>
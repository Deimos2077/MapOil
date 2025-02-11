<!-- upload.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Импорт Excel в MySQL</title>
</head>
<body>
    <h2>Загрузите Excel-файл для импорта данных</h2>
    <form action="process.php" method="post" enctype="multipart/form-data">
        <input type="file" name="excelFile" accept=".xlsx, .xls" required>
        <button type="submit">Импортировать</button>
    </form>
</body>
</html>
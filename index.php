<?php
        $host = 'localhost';
        $db = 'db_terem';
        $user = 'root';
        $password = '';
        $mysqli = new mysqli($host, $user, $password,  $db);

        /* проверка соединения */
        if (mysqli_connect_errno()) {
            printf("Не удалось подключиться: %s\n", mysqli_connect_error());
            exit();
        }
        mysqli_set_charset($mysqli, "utf8");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur."/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Тест</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

    <!--    <link rel="stylesheet" type="text/css" href="dist/css/styles.css" />-->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php
if (empty($_GET["page"])){

    $lines = file('data.csv');
    foreach ($lines as $line_num => $line) {
        $l = explode(";", $line);
        $data[$l[0]] = $l;
    }

    foreach ($data as $line_num => $line) {
        $convertedText = mb_convert_encoding($line[1], 'utf-8', 'cp-1251');

        if ($stmt = $mysqli->prepare("INSERT INTO test (id, name, value1, value2, value3) values(?, ?, ?, ?, ?)")) {
            $stmt->bind_param("issss", $line[0], $convertedText, $line[2], $line[3], $line[4]);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    }

    echo "
        <h2>Перейти на страницу</h2>
        <form method=\"get\" action=\"index.php\">
            <input type=\"text\" name=\"page\">
            <input type=\"submit\">
        </form>";
} else {
    if ( $_GET["page"] )  {
        if ($stmt2 = $mysqli -> prepare( "SELECT * FROM test")){
            $stmt2 -> execute();
            $result2 = $stmt2 -> get_result() ->fetch_all(MYSQLI_ASSOC);
        }
        /* Пагинация*/
        $count_pages =  intval((count($result2) - 1) / 20) + 1;
        $active = $_GET["page"];
        $count_show_pages = 20;
        $url = "/terem/index.php?page=1";
        $url_page = "/terem/index.php?page=";
        if ($count_pages > 1) {
            $left = $active - 1;
            $right = $count_pages - $active;
            if ($left < floor($count_show_pages / 2))
                $start = 1;
            else $start = $active - floor($count_show_pages / 2);
            $end = $start + $count_show_pages - 1;
            if ($end > $count_pages) {
                $start -= ($end - $count_pages);
                $end = $count_pages;
                if ($start < 1)
                    $start = 1;
            }

            echo "<h1>Таблица</h1>

<div class=\"container\">
    <div class=\"row1\">ID</div>
    <div class=\"row2\">NAME</div>
    <div class=\"row3\">VALUE1</div>
    <div class=\"row4\">VALUE2</div>
    <div class=\"row5\">VALUE3</div>
</div>";

            ?>
            <!-- навигационная панель и вывод данных в таблицу -->
            <div id="pagination">
                <span>Страницы: </span>
                <?php if ($active != 1) { ?>
                    <a href="<?=$url?>" title="Первая страница">&lt;&lt;&lt;</a>
                    <a href="<?php if ($active == 2) { ?><?=$url?><?php } else { ?><?=$url_page.($active - 1)?><?php } ?>" title="Предыдущая страница">&lt;</a>
                <?php } ?>
                <?php for ($i = $start; $i <= $end; $i++) { ?>
                    <?php if ($i == $active) { ?><span><?=$i?></span><?php } else { ?><a href="<?php if ($i == 1) { ?><?=$url?><?php } else { ?><?=$url_page.$i?><?php } ?>"><?=$i?></a><?php } ?>
                <?php } ?>
                <?php if ($active != $count_pages) { ?>
                    <a href="<?=$url_page.($active + 1)?>" title="Следующая страница">&gt;</a>
                    <a href="<?=$url_page.$count_pages?>" title="Последняя страница">&gt;&gt;&gt;</a>
                <?php }
                $count = 20;// Количество записей на странице
                $page = $_GET["page"];// Узнаём номер страницы
                $shift = $count * ($page - 1);// Смещение в LIMIT. Те записи, порядковый номер которого больше этого числа, будут выводиться.
                if ($stmt2 = $mysqli -> prepare( "SELECT * FROM test LIMIT $shift, $count")) {
                    $stmt2 -> execute();
                    $result2 = $stmt2 -> get_result() ->fetch_all(MYSQLI_ASSOC);
                }
                for ($i = 0 ; $i < count($result2) ; ++$i) {
                    if ($i % 2 == 0) {
                        echo "
                <div class=\"container\" style=\"background: #B0C2F1\">
                    <div class=\"row1\">" . $result2[$i]['id'] ."</div>
                    <div class=\"row2\">" . $result2[$i]['name'] ."</div>
                    <div class=\"row3\">" . $result2[$i]['value1'] ."</div>
                    <div class=\"row4\">" . $result2[$i]['value2'] ."</div>
                    <div class=\"row5\">" . $result2[$i]['value3'] ."</div>
                </div>";
                    } else { echo "
                    <div class=\"container\" style=\"background: #F3E7E4\">
                        <div class=\"row1\">" . $result2[$i]['id'] ."</div>
                        <div class=\"row2\">" . $result2[$i]['name'] ."</div>
                        <div class=\"row3\">" . $result2[$i]['value1'] ."</div>
                        <div class=\"row4\">" . $result2[$i]['value2'] ."</div>
                        <div class=\"row5\">" . $result2[$i]['value3'] ."</div>
                    </div>";
                    }
                } ?>
            </div>
            <?php
        }
        $mysqli -> close();
    }
}

?>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>


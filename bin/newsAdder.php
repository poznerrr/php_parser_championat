<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli') {
    echo "Скрипт запущен...\n";
} else {
    die("Запустите скрипт из консоли");
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

$client = new Predis\Client();

const CONTENT_FOLDER = 'content';

$dbConfig = require(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'dbConfig.php');
$pdo = new PDO($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['opts']);

while (true) {
    $path = $client->lpop('slamdunk_links');
    if ($path !== null) {
        $content = file_get_contents($path);

        //поиск тайтла
        preg_match_all("/<h1 class='ipsType_pageTitle(.*?)h1>/s", $content, $infoLines);
        $title = $infoLines[0][0] ?? null;
        if (!is_null($title)) {
            preg_match_all("/<div class='ipsType_break ipsContained'>(.*?)<\/div>/s", $title, $infoLines);
            $title = $infoLines[0][0];
            $title = str_replace("<div class='ipsType_break ipsContained'>", '', $title);
            $title = str_replace("</div>", '', $title);
        }


        //поиск основной новости
        preg_match_all("/<h1 class='ipsType_pageTitle(.*?)<\/p>\n<div/s", $content, $infoLines);
        $text = $infoLines[0][0] ?? null;
        if (!is_null($text)) {
            preg_match_all("/<p>.*<\/p>/s", $text, $infoLines);
            $text = $infoLines[0][0];
            $text = str_replace("</p>", '', $text);
            $text = str_replace("<p>", '', $text);
        }

        //запись в бд
        if (!is_null($title) && !is_null($text)) {
            $sql = "INSERT INTO Posts (title, post_text, user_id, category_id, post_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([$title, $text, 1, 1, time()]);
            } catch (Exception $ex) {
                echo "$i: {$ex->getMessage()} \n";
            }
            echo "$path записан\n";
        } else {
            echo "$path: отсутствует содержимое, либо неверный формат\n";
        }

    } else {
        echo "Скрипт завершил работу: в очереди нету данных";
        break;
    }
}





<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli') {
    echo "Скрипт запущен...\n";
} else {
    die("Запустите скрипт из консоли");
}

const CONTENT_FOLDER = 'content';

$dbConfig = require(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'dbConfig.php');
$pdo = new PDO($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['opts']);

$linkListPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . CONTENT_FOLDER . DIRECTORY_SEPARATOR . 'linkList.txt';

$linkList = fopen($linkListPath, 'r');

const FIRST_LINK = 900;
const LAST_LINK = 1000;

$file = new SplFileObject($linkListPath);
$file->seek(FIRST_LINK);

for ($i = FIRST_LINK; $i <= LAST_LINK; $i++) {
    $path = str_replace("\n", '', $file->current());
    //echo file_get_contents($path);
    /* Попробовал через curl
    $ch = curl_init($path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $content =curl_exec($ch);
    curl_close($ch);
    */
    $content = file_get_contents("$path");

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

    if (!is_null($title) && !is_null($text)) {
        $sql = "INSERT INTO Posts (title, post_text, user_id, category_id, post_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$title, $text, 1, 1, time()]);
        } catch (Exception $ex) {
            echo "$i: {$ex->getMessage()} \n";
        }
        echo "$i записан\n";
    } else {
        echo "$i: отсутствует содержимое, либо неверный формат\n";
    }


    $file->next();
}





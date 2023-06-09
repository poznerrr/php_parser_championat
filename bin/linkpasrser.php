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
const FIRST_AVAILABLE_PAGE = 1;
const LAST_AVAILABLE_PAGE = 4000;

$pageFolder = dirname(__DIR__) . DIRECTORY_SEPARATOR . CONTENT_FOLDER;

for ($i = FIRST_AVAILABLE_PAGE; $i <= LAST_AVAILABLE_PAGE; $i++) {
    $file = file_get_contents($pageFolder . DIRECTORY_SEPARATOR . $i . '.html');
    preg_match_all("/<div class='ipsType_break'>[^>]+/s", $file, $infoLines);
    $matches = $infoLines[0];
    foreach ($matches as &$match) {
        if (str_contains($match, 'https://www.slamdunk.ru/news')) {
            $match = str_replace("<div class='ipsType_break'>\n", '', $match);
            $match = str_replace("<a href=\"", '', trim($match));
            $match = str_replace("\"", "", $match);
            $client->rpush("slamdunk_links", $match);
        }

    }
}
echo "Скрипт завершил работу. В очереди {$client->llen("slamdunk_links")} ссылок";



<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli') {
    echo "Скрипт запущен...\n";
} else {
    die("Запустите скрипт из консоли");
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

$client = new Predis\Client();
const BASE_URL = "https://www.slamdunk.ru/index.php?ajaxpages/paginator&page=";
const CONTENT_FOLDER = 'content';
const FIRST_AVAILABLE_PAGE = 1;
const LAST_AVAILABLE_PAGE = 100;


(function (): void {
    try {
        for ($page = FIRST_AVAILABLE_PAGE; $page <= LAST_AVAILABLE_PAGE; $page++) {
            $pagePath = BASE_URL . $page;
            $path = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . CONTENT_FOLDER . DIRECTORY_SEPARATOR . $page . '.html';
            if (file_exists($path)) {
                continue;
            }
            file_put_contents($path, file_get_contents("$pagePath"));
        }
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage() . "\n";
    } finally {
        echo 'скрипт завершил работу.';
    }
})();

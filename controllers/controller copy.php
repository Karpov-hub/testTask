<?php

// Подключаем библиотеку GuzzleHttp
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;

function searchProducts($searchTerm) {
    // Создаем экземпляр клиента GuzzleHttp
    $client = new Client();

    // Отправляем POST запрос на сайт для поиска товаров
    $response = $client->request('POST', 'https://www.autozap.ru/goods', [
        'form_params' => [
            'code' => $searchTerm,
            'count' => '300',
            'page' => '1',
            'search' => 'Найти'
        ],
        'verify' => false
    ]);
print_r($response);
    // Получаем тело ответа и преобразуем его в строку
    $body = $response->getBody()->getContents();

    // Создаем объекты парсера DOM
    $dom = new DOMDocument();
    @$dom->loadHTML($body); // Подавляем ошибки, чтобы парсер не ругался на некорректный HTML

    // Создаем объекты для работы с XPath
    $xpath = new DOMXPath($dom);

    // Используем XPath для поиска нужных элементов на странице
    $items = $xpath->query("//div[@class='search-list']/div[contains(@class, 'product-list__item')]");

    $results = [];

    // Обходим найденные товары и извлекаем нужные данные
    foreach ($items as $item) {
        // Проверяем, что элемент является объектом DOMElement
        if ($item instanceof DOMElement) {
            // Получаем бренд товара
            $brand = '';
            $brandNode = $xpath->query(".//div[@class='product__brand']", $item)->item(0);
            if ($brandNode instanceof DOMElement) {
                $brand = $brandNode->textContent;
            }

            // Получаем наименование товара
            $name = '';
            $nameNode = $xpath->query(".//div[@class='product__name']", $item)->item(0);
            if ($nameNode instanceof DOMElement) {
                $name = $nameNode->textContent;
            }

            // Формируем массив данных для текущего товара
            $productData = [
                'brand' => $brand,
                'name' => $name
            ];

            // Добавляем данные о товаре в результаты
            $results[] = $productData;
        }
    }
    return $results;
}

// Пример вызова функции
$searchTerm = '17177';
$result = searchProducts($searchTerm);
var_dump($result);
?>

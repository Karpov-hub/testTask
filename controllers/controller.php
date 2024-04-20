<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

function searchProducts($requestBody) {
    // Преобразуем тело запроса из JSON в массив PHP
    $requestData = json_decode($requestBody, true);

    // Создаем экземпляр клиента GuzzleHttp
    $client = new Client();
    // Отправляем POST запрос на сайт для поиска товаров
    $response = $client->request('POST', 'https://www.autozap.ru/goods', [
        'form_params' => [
            'code' => $requestData['searchTerm'],
            'count' => $requestData['count'],
            'page' => $requestData['page'],
            'search' => $requestData['search']
        ],
        'verify' => false
    ]);

    // Получаем тело ответа и преобразуем его в строку
    $htmlContent = $response->getBody()->getContents();

    // Создаем объект Crawler
    $crawler = new Crawler($htmlContent);

    // Извлекаем данные о товарах
    $products = $crawler->filter('tr')->each(function (Crawler $node, $i) {
    $product = [];
    // Получаем данные о товаре
    $product['producer'] = $node->filter('.producer')->text();
    // $product['code'] = $node->filter('.code')->text();
    $product['name'] = $node->filter('.name')->text();
    // $product['price'] = $node->filter('.price')->text();
    $product['code'] = $node->filter('.code')->text();
    // $product['brand'] = $node->filter('.brand')->text();
    // $product['count'] = $node->filter('.count')->text();
    // $product['time'] = $node->filter('.time')->text();
    // $product['id'] = $node->filter('.id')->text();

// Выводим полученные данные о товарах
print_r($product);
    return $product;
});
}
?>

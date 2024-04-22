<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
function identifyResponseType($htmlContent) {
    // Проверяем наличие уникальных элементов в HTML-коде, которые могут отличаться в разных вариантах ответа
    if (strpos($htmlContent, 'Выберите производителя:') !== false)
        return 0;
    else 
        return 1;
}
function getProductDate($crawler) {
    // Извлекаем данные о товарах
    $products = $crawler->filter('tr')->slice(2)->each(function (Crawler $node, $i) {
        $product = [];
        // Получаем данные о товаре
        $product['name'] = $node->filter('.name')->count() > 0 ? $node->filter('.name')->text() : 'Нет данных';
        $product['article'] = $node->filter('.code')->count() > 0 ? $node->filter('.code')->text() : 'Нет данных';
        $product['price'] = $node->filter('.price')->count() > 0 ? $node->filter('.price')->children()->first()->text() : 'Нет данных';
        $product['count'] = $node->filter('.storehouse')->count() > 0 ? $node->filter('.storehouse')->text() : 'Нет данных';
        $product['time'] = $node->filter('.article')->count() > 0 ? $node->filter('.article')->text() : 'Нет данных';

        $brandText = $node->filter('.producer')->text();
        $spanText = $node->filter('.producer span')->text();
        $brand = str_replace($spanText, '', $brandText);
        $product['brand'] = !empty(trim($brand)) ? $brand : 'Нет данных';

        $product['id'] = $node->filter('.storehouse-quantity')->each(function (Crawler $quantityNode, $i) {
            // Ищем элемент input с атрибутом id, начинающимся с "g"
            $idNode = $quantityNode->filter('input[id^="g"]');
            // Проверяем, найден ли такой элемент и есть ли у него значение атрибута value
            if ($idNode->count() > 0 && $idNode->attr('value') !== null) {
                // Получаем значение атрибута value
                return $idNode->attr('value');
            }
            // Если элемент не найден или значение атрибута не удалось получить, возвращаем 'Нет данных'
            return 'Нет данных';
        });
        $product['id'] = !empty($product['id']) ? $product['id'][0] : 'Нет данных';
        return $product;
    });
    return $products;
}
function fileRecording($productDate) {
    // Преобразуем массив данных в формат JSON
    $jsonData = json_encode($productDate, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Путь к файлу, в который будем сохранять данные
    $filePath = __DIR__ . '../../date/res.json';

    // Записываем данные в файл
    file_put_contents($filePath, $jsonData);

    if (file_put_contents($filePath, $jsonData) === false) {
        echo "Не удалось записать данные в файл $filePath";
    } else {
        echo "Данные успешно записаны в файл: $filePath";
    }
    
    // Возвращаем путь к созданному файлу
    return $filePath;
}
function searchProducts($requestBody) {
    // Преобразуем тело запроса из JSON в массив PHP
    $requestData = json_decode($requestBody, true);

    // Создаем экземпляр клиента GuzzleHttp
    $client = new Client();

    // Определение базового URL
    $baseUrl = 'https://www.autozap.ru/goods';

    // Проверка, был ли передан параметр 'brand'
    if (isset($requestData['brand'])) {
        // Добавление значения 'brand' к базовому URL
        $baseUrl .= '/' . $requestData['searchTerm'];
        $baseUrl .= '/' . $requestData['brand'];
    }

    // Формирование запроса с учетом динамически сформированного URL
    $response = $client->request('POST', $baseUrl, [
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

    $identify = identifyResponseType($htmlContent); // определяем тип ответа true - страница товара, false - страница производителей
    
    // Создаем объект Crawler
    $crawler = new Crawler($htmlContent);

    if ($identify) {
        // Если это страница товара, возвращаем данные о продукте
        $productDate = getProductDate($crawler);
        fileRecording($productDate);
    } else {
        // Если это страница производителей, извлекаем название первого производителя
        $products = $crawler->filter('tr')->slice(1)->each(function (Crawler $node, $i) {
            $product = [];

            $brandText = $node->filter('.producer')->text();
            $spanText = $node->filter('.producer span')->text();
            $brand = str_replace($spanText, '', $brandText);
            $product['brand'] = !empty(trim($brand)) ? $brand : 'Нет данных';
            return $product;
        });
        
        // Добавляем название производителя к параметрам запроса, приводя его к нижнему регистру
        $requestData['brand'] = strtolower($products[0]['brand']);

        // Вызываем функцию searchProducts() с обновленными параметрами запроса
        return searchProducts(json_encode($requestData));
    }
}
?>

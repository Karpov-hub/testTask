    <?php

    // Подключаем библиотеку GuzzleHttp
    require_once 'vendor/autoload.php';

    use GuzzleHttp\Client;

    function searchProducts($searchTerm) {
        // Создаем экземпляр клиента GuzzleHttp
        $client = new Client();
    
        // Отправляем GET запрос на сайт для поиска товаров
        $response = $client->request('GET', 'https://www.autozap.ru/', [
            'query' => ['search' => $searchTerm],
            'verify' => false
        ]);
    
        // Получаем тело ответа и преобразуем его в строку
        $body = $response->getBody()->getContents();
    
        // Создаем объект парсера DOM
        $dom = new DOMDocument();
        @$dom->loadHTML($body); // Подавляем ошибки, чтобы парсер не ругался на некорректный HTML
    
        // Создаем объект для работы с XPath
        $xpath = new DOMXPath($dom);
    
        // Используем XPath для поиска нужных элементов на странице
        $items = $xpath->query("//div[@class='search-list']/div[contains(@class, 'product-list__item')]");
    
        $results = [];
    
        // Обходим найденные товары и извлекаем нужные данные
        foreach ($items as $item) {
            // Проверяем, что элемент является объектом DOMElement
            if ($item instanceof DOMElement) {
                $article = $item->getAttribute('data-product-id');
                $brandNode = $xpath->query(".//div[@class='product__brand']", $item)->item(0);
                $nameNode = $xpath->query(".//div[@class='product__name']", $item)->item(0);
                $priceNode = $xpath->query(".//div[@class='product__price']", $item)->item(0);
                $availabilityNode = $xpath->query(".//div[@class='product__availability']", $item)->item(0);
                $deliveryTimeNode = $xpath->query(".//div[@class='product__delivery']", $item)->item(0);
                $input = $xpath->query(".//input[starts-with(@id, 'g')]", $item)->item(0);
                $offerCode = $input ? $input->getAttribute('value') : '';
        
                // Проверяем, что элементы существуют и являются объектами DOMElement перед вызовом getAttribute()
                $brand = $brandNode instanceof DOMElement ? $brandNode->textContent : '';
                $name = $nameNode instanceof DOMElement ? $nameNode->textContent : '';
                $price = $priceNode instanceof DOMElement ? $priceNode->textContent : '';
                $availability = $availabilityNode instanceof DOMElement ? $availabilityNode->textContent : '';
                $deliveryTime = $deliveryTimeNode instanceof DOMElement ? $deliveryTimeNode->textContent : '';
        
                // Формируем массив данных для текущего товара
                $productData = [
                    'article' => $article,
                    'brand' => $brand,
                    'name' => $name,
                    'price' => $price,
                    'availability' => $availability,
                    'deliveryTime' => $deliveryTime,
                    'offerCode' => $offerCode
                ];
        
                // Добавляем данные о товаре в результаты
                $results[] = $productData;
            }
        }
    
        return $results;
    }
    

    // Пример вызова функции
    $searchTerm = 'Ваш поисковый запрос';
    $result = searchProducts($searchTerm);
    var_dump($result);

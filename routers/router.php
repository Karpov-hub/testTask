<?php
$routes = [
    '/get-products' => function($requestBody) {
        // Подключаем файл с контроллером
        require_once 'controllers/controller.php';
        // Вызываем функцию searchProducts, передавая тело запроса
        searchProducts($requestBody);
    },
];

// Получим запрошенный URI
$uri = $_SERVER['REQUEST_URI'];

// Получим тело запроса
$requestBody = file_get_contents('php://input');

// Проверим, существует ли маршрут для данного URI
if (array_key_exists($uri, $routes)) {
    // Если существует, вызываем соответствующую функцию или контроллер,
    // передавая тело запроса в качестве аргумента
    $routes[$uri]($requestBody);
} else {
    // В противном случае выведите сообщение об ошибке
    echo "404 Not Found";
}
?>

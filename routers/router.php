<?php
$routes = [
    '/get-products' => 'controllers/controller.php',
];


// Получим запрошенный URI
$uri = $_SERVER['REQUEST_URI'];

// Проверим, существует ли маршрут для данного URI
if (array_key_exists($uri, $routes)) {
    // Если существует, перенаправим запрос в соответствующий контроллер
    require_once $routes[$uri];
} else {
    // В противном случае выведите сообщение об ошибке
    echo "404 Not Found";
}

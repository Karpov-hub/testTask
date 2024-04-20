<?php

// Подключаем библиотеку для валидации JSON схем
require_once 'vendor/autoload.php';
use JsonSchema\Validator;

// Читаем содержимое файла schema.json
$schemaJson = file_get_contents('routers/schema.json');

// Декодируем JSON в ассоциативный массив
$schema = json_decode($schemaJson, true);

// Получим запрошенный URI
$uri = $_SERVER['REQUEST_URI'];

// Проверим, существует ли маршрут для данного URI
if (array_key_exists($uri, $schema)) {
    // Если существует, считываем данные запроса
    $requestData = json_decode(file_get_contents('php://input'), true);

    // Проверяем данные запроса с помощью схемы
    $validator = new Validator();
    $validator->validate($requestData, (object)$schema[$uri]);

    // Если данные не прошли валидацию, возвращаем ошибку
    if (!$validator->isValid()) {
        http_response_code(400);
        echo json_encode(["error" => "Ошибка валидации"]);
        exit;
    }

    // Если данные прошли валидацию, продолжаем выполнение запроса
    // Обрабатываем запрос в соответствующем контроллере или делаем что-то еще
    // Например:
    require_once 'routers/router.php';
} else {
    // Если маршрут не существует, возвращаем ошибку
    http_response_code(404);
    echo json_encode(["error" => "Маршрут не существует"]);
}

?>

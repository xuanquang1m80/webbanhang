<?php
require 'vendor/autoload.php';

// Lấy và xử lý URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);


if (isset($url[0]) && $url[0] === 'api' && isset($url[1])) {
    $apiControllerName = ucfirst($url[1]) . 'ApiController';

    if (file_exists('app/controllers/' . $apiControllerName . '.php')) {
        require_once 'app/controllers/' . $apiControllerName . '.php';
        $controller = new ("App\\Controllers\\" . $apiControllerName)();
        $method = $_SERVER['REQUEST_METHOD'];
        $id = $url[2] ?? null;

        // Xác định action dựa trên HTTP method  
        switch ($method) {
            case 'GET':
                $action = $id ? 'show' : 'index';
                break;
            case 'POST':
                if ($id) {
                    $action = 'update';
                }
                else{
                    $action = 'store';
                }
                break;
            case 'PUT':
                if ($id) {
                    $action = 'update';
                }
                break;
            case 'DELETE':
                if ($id) {
                    $action = 'destroy';
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method Not Allowed']);
                exit;
        }

        // Kiểm tra và gọi action
        if (method_exists($controller, $action)) {
            if ($id) {
                call_user_func_array([$controller, $action], [$id]);
            } else {
                call_user_func_array([$controller, $action], []);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Action not found']);
        }
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Controller not found']);
        exit;
    }
} else {
    // Xử lý non-API request hoặc route khác nếu cần
    $controllerName = isset($url[0]) && $url[0] !== '' ? ucfirst($url[0]) . 'Controller' : 'DefaultController';
    $action = isset($url[1]) && $url[1] !== '' ? $url[1] : 'index';

    try {
        $controllerClass = "App\\Controllers\\" . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new Exception('Controller not found');
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new Exception('Action not found');
        }

        // Gọi action với tham số còn lại (nếu có)
        call_user_func_array([$controller, $action], array_slice($url, 2));
    } catch (Exception $e) {
        http_response_code(404);
        echo $e->getMessage();
        exit;
    }
}




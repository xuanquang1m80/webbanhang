<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public static function verifyToken($bearerToken)
    {
        
        // Nạp file cấu hình
        $config = include 'app/config/jwt.php';

        // Kiểm tra xem token có tồn tại không
        if (empty($bearerToken)) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized. Token không hợp lệ.'
            ]);
            exit;
        }
        $token = substr($bearerToken, 7);
        try {
            // Giải mã token
            $decoded = JWT::decode($token, new Key($config['secret_key'], 'HS256'));

            // Kiểm tra tính hợp lệ của token
            if (
                $decoded->iss !== $config['issuer'] ||
                $decoded->aud !== $config['audience']
            ) {
                http_response_code(401); // Unauthorized
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorized. Token không hợp lệ.'
                ]);
                exit;
            }

            // Trả về dữ liệu đã giải mã
            return (array) $decoded->data;
        } catch (\Exception $e) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized. ' . $e->getMessage()
            ]);
            exit;
        }
    }


}

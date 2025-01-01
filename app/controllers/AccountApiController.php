<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Config\Database;
use Firebase\JWT\JWT;

class AccountApiController
{

    private $accountModel;
    private $db;


    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    public function Login()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            // Tìm tài khoản trong database
            $account = $this->accountModel->getAccountByUserName($username);

            if ($account) {
                $pwd_hashed = $account->password;

                // Kiểm tra mật khẩu
                if (password_verify($password, $pwd_hashed)) {
                    $roles = $this->accountModel->getRolesByUserId($account->id);

                    // Tạo payload cho token
                    $config = include 'app/config/jwt.php';
                    $issuedAt = time();
                    $expirationTime = $issuedAt + $config['expiration_time']; // Token hết hạn sau 1 giờ

                    $payload = [
                        'iss' => $config['issuer'],       // Issuer
                        'aud' => $config['audience'],    // Audience
                        'iat' => $issuedAt,              // Issued at
                        'exp' => $expirationTime,        // Expiration time
                        'data' => [
                            'user_id' => $account->id,
                            'username' => $account->username,
                            'roles' => $roles
                        ],
                    ];

                    // Tạo token
                    $jwt = JWT::encode($payload, $config['secret_key'], 'HS256');

                    // Trả về token
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'token' => $jwt,
                        'username' => $account->username,
                    ]);
                    exit;
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Password incorrect'
                    ]);
                    exit;
                }
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Account not found'
                ]);
                exit;
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
            exit;
        }
    }



    public function Register()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            // Kiểm tra dữ liệu đầu vào
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Username and password are required'
                ]);
                exit;
            }

            // Kiểm tra tài khoản đã tồn tại
            $existingAccount = $this->accountModel->getAccountByUserName($username);

            if ($existingAccount) {
                http_response_code(409);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Username already exists'
                ]);
                exit;
            }

            // Hash mật khẩu
            $passwordHashed = password_hash($password, PASSWORD_BCRYPT);

            // Tạo tài khoản mới
            $newAccount = [
                'username' => $username,
                'password' => $passwordHashed
            ];

            $userId = $this->accountModel->createAccount($newAccount);

            if ($userId) {
                $roleId = 3;
                $this->accountModel->assignRoleToUser($userId, $roleId);

                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Account created successfully'
                ]);
                exit;
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create account'
                ]);
                exit;
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
            exit;
        }
    }
}

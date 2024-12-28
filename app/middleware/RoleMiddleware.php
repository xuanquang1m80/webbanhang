<?php

namespace App\Middleware;
use App\Helpers\RoleHelper;
use App\Helpers\SessionHelper;

class RoleMiddleware {
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // public function handle() {
    //    // Kiểm tra người dùng đã đăng nhập chưa
    //     if (!SessionHelper::isLoggedIn()) {
    //         header('Location: /lab_1/Account/login');
    //         exit;
    //     }

    //     // Kiểm tra quyền admin
    //     $roleHelper = new RoleHelper($this->db);
    //     if (!$roleHelper->userHasRole($_SESSION['user_id'], 'Admin')) {
    //         header('Location: /lab_1/Product/forbiden');
    //         exit;
    //     }
    // }

    public function handle() {
        // Kiểm tra xem có cookie lưu trữ thông tin người dùng không
        if (!isset($_COOKIE['user_data'])) {
            // Nếu không có cookie, điều hướng đến trang đăng nhập
            header('Location: /lab_1/Account/login');
            exit;
        }

        // Giải mã cookie và lấy thông tin người dùng
        $userData = json_decode($_COOKIE['user_data'], true);
        if (!$userData || !isset($userData['user_id'], $userData['username'])) {
            // Nếu dữ liệu cookie không hợp lệ, điều hướng đến trang đăng nhập
            header('Location: /lab_1/Account/login');
            exit;
        }
        // Kiểm tra quyền admin
        $roleHelper = new RoleHelper($this->db);
        if (!$roleHelper->userHasRole($userData['user_id'], 'Admin')) {
            // Nếu người dùng không phải là admin, điều hướng đến trang forbidden
            header('Location: /lab_1/Product/forbiden');
            exit;
        }
    }



   
    
}

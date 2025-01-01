<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private $host = "localhost";
    private $db_name = "webbanhang";
    private $username = "root";
    private $password = "2610";
    public $conn;
    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
    public function deleteExpiredCartItems()
    {
        // Kết nối cơ sở dữ liệu
        $conn = $this->getConnection();

        // Câu lệnh SQL để xóa các mục giỏ hàng quá 30 ngày
        $query = "DELETE FROM cart_items WHERE created_at < NOW() - INTERVAL 1 MONTH";

        // Thực thi câu lệnh SQL
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            // echo "Expired cart items have been deleted.\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}


// Tạo đối tượng Database và gọi phương thức xóa mục hết hạn
$db = new Database();
$db->deleteExpiredCartItems();

<?php

namespace App\Models;

use PDO;


class AccountModel
{

    private $conn;
    private $table_name = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAccountByUsername($username)
    {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    function save($username, $name, $password, $role = "user")
    {
        //  $query = "INSERT INTO " . $this->table_name . "(username, password, role) VALUES (:username,:password, :role)";
        $query = "INSERT INTO " . $this->table_name . "(username, password) VALUES (:username,:password)";

        $stmt = $this->conn->prepare($query);
        // Làm sạch dữ liệu
        $name = htmlspecialchars(strip_tags($name));
        $username = htmlspecialchars(strip_tags($username));
        // Gán dữ liệu vào câu lệnh
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        // $stmt->bindParam(':role', $role);
        // Thực thi câu lệnh
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function createAccount($accountData)
    {
        $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $accountData['username']);
        $stmt->bindParam(':password', $accountData['password']);

        return $stmt->execute();
    }


    public function assignRoleToUser($userId, $roleId)
    {
        $query = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':role_id', $roleId);

        return $stmt->execute();
    }

    public function getRolesByUserId($userId)
    {
        $query = "SELECT r.name FROM user_roles ur
              JOIN roles r ON ur.role_id = r.id
              WHERE ur.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Lấy danh sách roles dưới dạng mảng
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

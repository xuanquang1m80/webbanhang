<?php

namespace App\Models;

use PDO;

class ProductModel
{

    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addProduct($name, $description, $price, $category_id, $image)
    {

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }
        if (empty($description)) {
            $errors['description'] = 'Mô tả không được để trống';
        }
        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }
        if (count($errors) > 0) {
            return $errors;
        }


        $query = "INSERT INTO " . $this->table_name . " (name, description, price,
        category_id,image) VALUES (:name, :description, :price, :category_id, :image)";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function updateProduct($id, $name, $description, $price, $category_id, $image)
    {

        $query = "UPDATE " . $this->table_name . " SET name=:name,
        description=:description, price=:price, category_id=:category_id, image=:image WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }



    public function getProducts()
    {
        $query = "SELECT p.id, p.name, p.description, p.price,p.image, c.name as category_name
    FROM " . $this->table_name . " p
    LEFT JOIN category c ON p.category_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $result;
    }

    public function getProductById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }


    public function deleteProduct($id)
    {
        // try {
        $this->conn->beginTransaction();

        // Xóa khỏi cart_items trước
        $query = "DELETE FROM cart_items WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Xóa sản phẩm
        $query = "DELETE FROM product WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $this->conn->commit();
        return true;
        // } catch (Exception $e) {
        //     $this->conn->rollBack();
        //     return false;
        // }
    }


    public function getOrdersByUserId($userId)
    {
        $query = "SELECT 
                orders.id AS order_id,
                orders.name AS order_name,
                orders.phone,
                orders.address,
                orders.created_at,
                product.name AS product_name, 
                order_details.quantity,
                order_details.price
              FROM 
                orders
              INNER JOIN 
                order_details ON orders.id = order_details.order_id
              INNER JOIN 
                product ON order_details.product_id = product.id
              WHERE 
                orders.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalProducts()
    {
        $query = "SELECT COUNT(*) AS total_products FROM product";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_products'];
    }


    public function getTotalOrders()
    {
        $query = "SELECT COUNT(*) AS total_orders FROM order_details";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_orders'];
    }
}

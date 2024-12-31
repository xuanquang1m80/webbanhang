<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Config\Database;
use App\Middleware\AuthMiddleware;
use Exception;
use Firebase\JWT\JWT;

class ProductApiController
{

    private $db;
    private $productModel;
    private $authMiddleware;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->authMiddleware = new AuthMiddleware();
    }


    // Lấy danh sách sản phẩm
    public function index()
    {
        header('Content-Type: application/json');
        $products = $this->productModel->getProducts();
        echo json_encode($products);
    }


    // Lấy thông tin sản phẩm theo ID
    public function show($id)
    {
        header('Content-Type: application/json');
        $product = $this->productModel->getProductById($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Product not found']);
        }
    }


    public function store()
    {
        header('Content-Type: application/json');

        // Kiểm tra phương thức HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
            exit;
        }

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Xác thực token
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }

        // Lấy dữ liệu từ body
        $input = json_decode(file_get_contents('php://input'), true);

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        $imageFile = $_FILES['image'] ?? null;

        // Kiểm tra dữ liệu cần thiết
        if (empty($name) || empty($price) || empty($imageFile)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Name, price, and image are required.']);
            exit;
        }

        // Kiểm tra loại tệp tin hình ảnh hợp lệ
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($imageFile['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Only JPEG, PNG, or GIF allowed.']);
            exit;
        }

        // Lưu tệp hình ảnh vào thư mục tạm hoặc thư mục uploads
        $uploadDir = 'public/images/';
        $imagePath = $uploadDir . basename($imageFile['name']);
        if (!move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
            exit;
        }

        // Chuyển đổi tệp hình ảnh sang base64
        $imageData = base64_encode(file_get_contents($imagePath));
        $imageBase64 = 'data:' . $imageFile['type'] . ';base64,' . $imageData;

        // Gọi hàm lưu dữ liệu vào database
        $result = $this->productModel->addProduct(
            $name,
            $description,
            $price,
            $category_id,
            $imageBase64
        );

        if (is_array($result)) {
            http_response_code(400);
            echo json_encode(['errors' => $result]);
        } else {
            http_response_code(201);
            echo json_encode(['message' => 'Product created successfully']);
        }
    }





    // Cập nhật sản phẩm theo ID
    // public function update($id)
    // {

    //     header('Content-Type: application/json');
    //     $data = json_decode(file_get_contents('php://input'), true);

    //     // Lấy token từ Header
    //     // $headers = getallheaders();
    //     // $token = $headers['Authorization'] ?? null;

    //     // // Xác thực token
    //     // $user = AuthMiddleware::verifyToken($token);

    //     // if (!$user) {
    //     //     http_response_code(401); // Unauthorized
    //     //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
    //     //     exit;
    //     // }

    //     $name = $data['name'] ?? '';
    //     $description = $data['description'] ?? '';
    //     $price = $data['price'] ?? '';
    //     $category_id = $data['category_id'] ?? null;
    //     $imageBase64 = $data['image'] ?? null;

    //     // Kiểm tra xem sản phẩm có tồn tại không
    //     $product = $this->productModel->getProductById($id);

    //     if (!$product) {
    //         http_response_code(404);
    //         echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    //         exit;
    //     }

    //     // Xử lý hình ảnh
    //     $imagePath = $product->image; // Giữ nguyên chuỗi base64 hiện tại
    //     if (!empty($imageBase64)) {
    //         $imageData = explode(',', $imageBase64);
    //         if (count($imageData) !== 2 || strpos($imageData[0], 'base64') === false) {
    //             http_response_code(400);
    //             echo json_encode(['status' => 'error', 'message' => 'Invalid image format.']);
    //             exit;
    //         }

    //         $imageInfo = explode(';', $imageData[0]);
    //         $mimeType = str_replace('data:', '', $imageInfo[0]);
    //         $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    //         if (!in_array($mimeType, $allowedTypes)) {
    //             http_response_code(400);
    //             echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Only JPEG, PNG, or GIF allowed.']);
    //             exit;
    //         }

    //         // Cập nhật chuỗi base64 mới
    //         $imagePath = $imageBase64;
    //     }

    //     $result = $this->productModel->updateProduct(
    //         $id,
    //         $name,
    //         $description,
    //         $price,
    //         $category_id,
    //         $imagePath
    //     );
    //     if ($result) {
    //         echo json_encode(['message' => 'Product updated successfully']);
    //     } else {
    //         http_response_code(400);
    //         echo json_encode(['message' => 'Product update failed']);
    //     }
    // }

    public function update($id)
    {
        header('Content-Type: application/json');

        // Kiểm tra phương thức HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
            exit;
        }

        // Lấy dữ liệu từ body
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        $imageFile = $_FILES['image'] ?? null;

        // Kiểm tra xem sản phẩm có tồn tại không
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
            exit;
        }

        // Xử lý hình ảnh
        $imagePath = $product->image; // Giữ nguyên chuỗi base64 hiện tại nếu không có file mới
        if (!empty($imageFile)) {
            // Kiểm tra loại tệp tin hình ảnh hợp lệ
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($imageFile['type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Only JPEG, PNG, or GIF allowed.']);
                exit;
            }

            // Lưu tệp hình ảnh vào thư mục tạm hoặc thư mục uploads
            $uploadDir = 'public/images/';
            $imagePathFile = $uploadDir . basename($imageFile['name']);
            if (!move_uploaded_file($imageFile['tmp_name'], $imagePathFile)) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
                exit;
            }

            // Chuyển đổi tệp hình ảnh sang base64
            $imageData = base64_encode(file_get_contents($imagePathFile));
            $imagePath = 'data:' . $imageFile['type'] . ';base64,' . $imageData;
        }

        // Cập nhật sản phẩm
        $result = $this->productModel->updateProduct(
            $id,
            $name,
            $description,
            $price,
            $category_id,
            $imagePath
        );

        if ($result) {
            echo json_encode(['message' => 'Product updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Product update failed']);
        }
    }





    // Xóa sản phẩm theo ID
    public function destroy($id)
    {

        header('Content-Type: application/json');


        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Xác thực token
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }
        $result = $this->productModel->deleteProduct($id);
        if ($result) {
            echo json_encode(['message' => 'Product deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Product deletion failed']);
        }
    }


    //Tạo mới giỏ hàng
    public function getOrCreateCart($userId)
    {
        $query = "SELECT id FROM carts WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cart = $stmt->fetch();

        if (!$cart) {
            $query = "INSERT INTO carts (user_id) VALUES (:user_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $this->db->lastInsertId();
        }

        return $cart['id'];
    }


    //Thêm vào sản phẩm vào giỏ hàng
    public function addToCart($productId)
    {
        $quantity = 1;

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }

        $userId = $user['user_id'];

        $cartId = $this->getOrCreateCart($userId);

        $query = "SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $cartItem = $stmt->fetch();

        if ($cartItem) {
            // Cập nhật số lượng
            $newQuantity = $cartItem['quantity'] + $quantity;
            $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':quantity', $newQuantity);
            $stmt->bindParam(':id', $cartItem['id']);
            $stmt->execute();
        } else {
            // Thêm sản phẩm mới
            $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, (SELECT price FROM product WHERE id = :product_id))";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();
        }

        http_response_code(201);
        echo json_encode(['message' => 'Added product to cart successfully']);
    }

    //Giảm số lượng theo sản phẩm 
    public function descrease($productId)
    {
        $quantity = 1;

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }

        $userId = $user['user_id'];

        $cartId = $this->getOrCreateCart($userId);

        $query = "SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $cartItem = $stmt->fetch();

        if ($cartItem) {
            // Cập nhật số lượng
            $newQuantity = $cartItem['quantity'] - $quantity;
            $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':quantity', $newQuantity);
            $stmt->bindParam(':id', $cartItem['id']);
            $stmt->execute();
        }
        http_response_code(201);
        echo json_encode(['message' => 'Descrease product successfully']);
    }

    //Lấy ra danh sách trong giỏ hàng 
    public function cart()
    {

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }

        $userId = $user['user_id'];

        $cartId = $this->getOrCreateCart($userId);

        $query = "SELECT ci.quantity, p.name, p.image,p.price 
              FROM cart_items ci 
              JOIN product p ON ci.product_id = p.id 
              WHERE ci.cart_id = :cart_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->execute();

        $cartItems = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'message' => 'Cart items retrieved successfully.',
            'data' => $cartItems
        ]);
    }


    public function processCheckout()
    {
        header('Content-Type: application/json');

        // Kiểm tra phương thức HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            return;
        }

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            return;
        }

        $userId = $user['user_id'];

        // Lấy thông tin khách hàng từ body của request
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;
        $address = $data['address'] ?? null;

        if (!$name || !$phone || !$address) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields (name, phone, address).']);
            return;
        }




        // Truy vấn giỏ hàng từ cơ sở dữ liệu
        $query = "
            SELECT ci.product_id, ci.quantity, p.price
            FROM cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            JOIN product p ON ci.product_id = p.id
            WHERE c.user_id = :user_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cartItems = $stmt->fetchAll();

        // Kiểm tra giỏ hàng có trống không
        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
            return;
        }

        // Bắt đầu transaction
        $this->db->beginTransaction();
        try {
            // Lưu thông tin đơn hàng vào bảng orders
            $query = "INSERT INTO orders (user_id, name, phone, address) VALUES (:user_id, :name, :phone, :address)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->execute();
            $orderId = $this->db->lastInsertId();

            // Lưu chi tiết đơn hàng vào bảng order_details
            foreach ($cartItems as $item) {
                $query = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
            }

            // Commit transaction
            $this->db->commit();

            // Trả về phản hồi thành công
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Checkout successful.',
                'order_id' => $orderId
            ]);
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred during checkout: ' . $e->getMessage()
            ]);
        }
    }


    public function getUserOrders()
    {
        header('Content-Type: application/json');

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Xác thực token
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }
        $userId = $user['user_id'];


        // Truy vấn danh sách đơn hàng của user
        $orders = $this->productModel->getOrdersByUserId($userId);

        if (!$orders) {
            echo json_encode(['status' => 'success', 'data' => []]);
            return;
        }

        // Trả về danh sách đơn hàng
        echo json_encode(['status' => 'success', 'data' => $orders]);
    }

    public function AllProduct()
    {
        header('Content-Type: application/json');

        // Lấy token từ Header
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;

        // // Xác thực token
        // $user = AuthMiddleware::verifyToken($token);

        // if (!$user) {
        //     http_response_code(401); // Unauthorized
        //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
        //     exit;
        // }
        $response = [
            'status' => 'success',
            'data' => [
                'total_products' => $this->productModel->getTotalProducts()
            ]
        ];
        echo json_encode($response);
    }

    public function AllOrders()
    {
        header('Content-Type: application/json');

        // Lấy token từ Header
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;

        // // Xác thực token
        // $user = AuthMiddleware::verifyToken($token);

        // if (!$user) {
        //     http_response_code(401); // Unauthorized
        //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
        //     exit;
        // }
        $response = [
            'status' => 'success',
            'data' => [
                'total_orders'  => $this->productModel->getTotalOrders()
            ]
        ];
        echo json_encode($response);
    }
}

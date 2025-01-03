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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
            exit;
        }

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
            exit;
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? '';
        $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== ''
            ? (int)$_POST['category_id']
            : null;
        $imageFile = $_FILES['image'] ?? null;

        $imagePath = $product->image;
        if (!empty($imageFile)) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($imageFile['type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid image type.']);
                exit;
            }

            $uploadDir = 'public/images/';
            $imagePathFile = $uploadDir . basename($imageFile['name']);
            if (!move_uploaded_file($imageFile['tmp_name'], $imagePathFile)) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
                exit;
            }

            $imageData = base64_encode(file_get_contents($imagePathFile));
            $imagePath = 'data:' . $imageFile['type'] . ';base64,' . $imageData;
        }

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
        header('Content-Type: application/json');
        $quantity = 1;

        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        // Verify product exists
        $query = "SELECT id FROM product WHERE id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
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

        try {
            if ($cartItem) {
                $newQuantity = $cartItem['quantity'] + $quantity;
                $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':quantity', $newQuantity);
                $stmt->bindParam(':id', $cartItem['id']);
                $stmt->execute();
            } else {
                $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price) 
                     SELECT :cart_id, :product_id, :quantity, price 
                     FROM product 
                     WHERE id = :product_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':cart_id', $cartId);
                $stmt->bindParam(':product_id', $productId);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->execute();
            }

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Product added to cart successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to add product to cart'
            ]);
        }
    }

    public function removeFromCart($productId)
    {
        header('Content-Type: application/json');

        // Verify authentication
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $userId = $user['user_id'];
        $cartId = $this->getOrCreateCart($userId);

        // Delete the cart item
        $query = "DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
        }
    }

    public function decrease($productId)
    {
        header('Content-Type: application/json');
        $quantity = 1;

        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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

        if (!$cartItem) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart']);
            exit;
        }

        $newQuantity = $cartItem['quantity'] - $quantity;

        if ($newQuantity <= 0) {
            $query = "DELETE FROM cart_items WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $cartItem['id']);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
            }
            exit;
        }

        $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':quantity', $newQuantity);
        $stmt->bindParam(':id', $cartItem['id']);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Quantity decreased successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to decrease quantity']);
        }
    }

    public function increase($productId)
    {
        header('Content-Type: application/json');
        $quantity = 1;

        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        $user = AuthMiddleware::verifyToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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

        if (!$cartItem) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart']);
            exit;
        }

        $newQuantity = $cartItem['quantity'] + $quantity;

        $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':quantity', $newQuantity);
        $stmt->bindParam(':id', $cartItem['id']);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Quantity increased successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to increase quantity']);
        }
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

        $query = "SELECT ci.quantity, p.id, p.name, p.image, p.price 
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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            return;
        }

        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            return;
        }

        $userId = $user['user_id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;
        $address = $data['address'] ?? null;

        if (!$name || !$phone || !$address) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
            return;
        }

        $query = "
            SELECT ci.product_id, ci.quantity, p.price
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN product p ON ci.product_id = p.id
            WHERE c.user_id = :user_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cartItems = $stmt->fetchAll();

        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $query = "INSERT INTO orders (user_id, name, phone, address) VALUES (:user_id, :name, :phone, :address)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->execute();
            $orderId = $this->db->lastInsertId();

            foreach ($cartItems as $item) {
                $query = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
            }

            $this->db->commit();

            // Chuẩn bị thông tin thanh toán
            $totalAmount = array_reduce($cartItems, function ($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);

            $paymentUrl = $this->initVNPayPayment($orderId, $totalAmount);

            echo json_encode([
                'status' => 'pending',
                'message' => 'Redirect to payment.',
                'payment_url' => $paymentUrl
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }


    private function initVNPayPayment($orderId, $amount)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_ReturnUrl = "http://localhost:3000/order-success"; // Change this
        $vnp_TmnCode = "WTD1ZKUP";  // Get from VNPay
        $vnp_HashSecret = "O7U8M9GUSSH8OG1RMM175PWTS4RWGYX1"; // Get from VNPay

        $vnp_TxnRef = $orderId; // Order ID
        $vnp_OrderInfo = "Payment for order #" . $orderId;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $amount * 100; // Convert to VND, no decimals
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return $vnp_Url;
    }


    public function handleVNPayReturn()
    {
        $responseCode = $_GET['vnp_ResponseCode'];
        $orderId = $_GET['vnp_TxnRef'];
        $transactionNo = $_GET['vnp_TransactionNo'];

        if ($responseCode == '00') { // Thanh toán thành công
            $query = "UPDATE orders SET payment_status = 'paid' WHERE id = :order_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();

            echo "Payment successful for Order ID: " . $orderId;
        } else { // Thanh toán thất bại
            echo "Payment failed. Please try again.";
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

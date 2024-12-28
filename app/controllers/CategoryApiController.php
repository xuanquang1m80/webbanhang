<?php
namespace App\Controllers;
use App\Config\Database;
use App\Models\CategoryModel;


class CategoryApiController
{

    private $categoryModel;
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }


    public function index()
    {
        header('Content-Type: application/json');

        try {
            $categories = $this->categoryModel->getCategories();
            echo json_encode(['status' => 'success', 'data' => $categories]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch categories.']);
        }
    }

    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            return;
        }

        try {
            $result = $this->categoryModel->createCategory($name, $description);
            if ($result) {
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Category created successfully.']);
            } else {
                throw new \Exception('Failed to create category.');
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            return;
        }

        try {
            $result = $this->categoryModel->updateCategory($id, $name, $description);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Category updated successfully.']);
            } else {
                throw new \Exception('Failed to update category.');
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        header('Content-Type: application/json');

        try {
            $result = $this->categoryModel->deleteCategory($id);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully.']);
            } else {
                throw new \Exception('Failed to delete category.');
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        header('Content-Type: application/json');

        try {
            $category = $this->categoryModel->getCategoryById($id);
            if ($category) {
                echo json_encode(['status' => 'success', 'data' => $category]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Category not found.']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

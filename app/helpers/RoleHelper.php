<?php
namespace App\Helpers;

class RoleHelper {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function userHasRole($userId, $roleName) {
        $query = "
            SELECT 1
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = :user_id AND r.name = :role_name
            LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':role_name', $roleName);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
}

<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include "../db_conn.php";
include "jwt_helper.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── POST /api/users.php  →  Login, get JWT ────────────────────────────────
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Email and password required"]);
            break;
        }

        // Query the admin table
        $stmt = $conn->prepare("SELECT id, email, role, password FROM admin WHERE email = ? LIMIT 1");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid email or password"]);
            break;
        }

        // Issue JWT
        $token = jwt_encode([
            "user_id" => $user['id'],
            "email"   => $user['email'],
            "role"    => $user['role'] ?? 'user',
            "name"    => $user['email'],
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "token"   => $token,
            "expires_in" => "24 hours",
            "user"    => [
                "id"    => $user['id'],
                "email" => $user['email'],
                "role"  => $user['role'] ?? 'admin'
            ]
        ]);
        break;

    // ── GET /api/users.php  →  Get my profile (protected) ────────────────────
    case 'GET':
        $payload = require_auth();

        $stmt = $conn->prepare("SELECT id, email, role, created_at FROM admin WHERE id = ?");
        $stmt->execute([$payload['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "User not found"]);
            break;
        }

        echo json_encode(["success" => true, "data" => $user]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>
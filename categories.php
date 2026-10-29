<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include "../db_conn.php";
include "jwt_helper.php";

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    // ── GET → public ──────────────────────────────────────────────────────────
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("
                SELECT c.*, COUNT(b.id) AS total_books
                FROM categories c
                LEFT JOIN books b ON b.category_id = c.id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$id]);
            $cat = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cat) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Category not found"]);
                break;
            }
            echo json_encode(["success" => true, "data" => $cat]);
        } else {
            $stmt = $conn->prepare("
                SELECT c.*, COUNT(b.id) AS total_books
                FROM categories c
                LEFT JOIN books b ON b.category_id = c.id
                GROUP BY c.id
                ORDER BY c.name ASC
            ");
            $stmt->execute();
            $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "count" => count($cats), "data" => $cats]);
        }
        break;

    // ── POST → admin only ─────────────────────────────────────────────────────
    case 'POST':
        require_admin();

        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Name required"]);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$data['name']]);

        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Category added", "id" => $conn->lastInsertId()]);
        break;

    // ── PUT → admin only ──────────────────────────────────────────────────────
    case 'PUT':
        require_admin();

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->execute([$data['name'] ?? '', $id]);

        echo json_encode(["success" => true, "message" => "Category updated"]);
        break;

    // ── DELETE → admin only ───────────────────────────────────────────────────
    case 'DELETE':
        require_admin();

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            break;
        }

        $conn->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        echo json_encode(["success" => true, "message" => "Category deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>
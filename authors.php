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
                SELECT a.*, COUNT(b.id) AS total_books
                FROM authors a
                LEFT JOIN books b ON b.author_id = a.id
                WHERE a.id = ?
                GROUP BY a.id
            ");
            $stmt->execute([$id]);
            $author = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$author) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Author not found"]);
                break;
            }
            echo json_encode(["success" => true, "data" => $author]);
        } else {
            $stmt = $conn->prepare("
                SELECT a.*, COUNT(b.id) AS total_books
                FROM authors a
                LEFT JOIN books b ON b.author_id = a.id
                GROUP BY a.id
                ORDER BY a.name ASC
            ");
            $stmt->execute();
            $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "count" => count($authors), "data" => $authors]);
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

        $stmt = $conn->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['bio'] ?? '']);

        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Author added", "id" => $conn->lastInsertId()]);
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
        $stmt = $conn->prepare("UPDATE authors SET name=?, bio=? WHERE id=?");
        $stmt->execute([$data['name'] ?? '', $data['bio'] ?? '', $id]);

        echo json_encode(["success" => true, "message" => "Author updated"]);
        break;

    // ── DELETE → admin only ───────────────────────────────────────────────────
    case 'DELETE':
        require_admin();

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            break;
        }

        $conn->prepare("DELETE FROM authors WHERE id = ?")->execute([$id]);
        echo json_encode(["success" => true, "message" => "Author deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>
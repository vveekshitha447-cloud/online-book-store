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

    // ── GET /api/books.php            → public, paginated, searchable ─────────
    // ── GET /api/books.php?id=5       → single book
    // ── GET /api/books.php?page=2&limit=10&search=harry&category_id=3
    case 'GET':
        if ($id) {
            // Single book with author + category + avg rating
            $stmt = $conn->prepare("
                SELECT b.*, a.name AS author, c.name AS category,
                       ROUND(AVG(r.rating), 1) AS avg_rating,
                       COUNT(r.id)             AS total_reviews
                FROM books b
                LEFT JOIN authors    a ON b.author_id    = a.id
                LEFT JOIN categories c ON b.category_id  = c.id
                LEFT JOIN reviews    r ON r.book_id       = b.id
                WHERE b.id = ?
                GROUP BY b.id
            ");
            $stmt->execute([$id]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Book not found"]);
                break;
            }
            echo json_encode(["success" => true, "data" => $book]);

        } else {
            // Paginated list with optional search + category filter
            $page       = max(1, (int)($_GET['page']        ?? 1));
            $limit      = min(50, max(1, (int)($_GET['limit'] ?? 10)));
            $offset     = ($page - 1) * $limit;
            $search     = trim($_GET['search']      ?? '');
            $categoryId = trim($_GET['category_id'] ?? '');

            $where  = "WHERE 1=1";
            $params = [];

            if ($search) {
                $where   .= " AND (b.title LIKE ? OR a.name LIKE ? OR b.description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($categoryId) {
                $where   .= " AND b.category_id = ?";
                $params[] = (int)$categoryId;
            }

            // Total count for pagination meta
            $countStmt = $conn->prepare("
                SELECT COUNT(DISTINCT b.id)
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                $where
            ");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Actual data
            $stmt = $conn->prepare("
                SELECT b.*, a.name AS author, c.name AS category,
                       ROUND(AVG(r.rating), 1) AS avg_rating,
                       COUNT(r.id)             AS total_reviews
                FROM books b
                LEFT JOIN authors    a ON b.author_id   = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN reviews    r ON r.book_id      = b.id
                $where
                GROUP BY b.id
                ORDER BY b.id DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([...$params, $limit, $offset]);
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "count"   => count($books),
                "data"    => $books,
                "pagination" => [
                    "page"        => $page,
                    "limit"       => $limit,
                    "total"       => $total,
                    "total_pages" => (int)ceil($total / $limit),
                    "has_next"    => $page < ceil($total / $limit),
                    "has_prev"    => $page > 1
                ]
            ]);
        }
        break;

    // ── POST /api/books.php  →  Add book (admin only) ─────────────────────────
    case 'POST':
        require_admin();

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['title']) || empty($data['author_id']) || empty($data['category_id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "title, author_id, category_id are required"]);
            break;
        }

        $stmt = $conn->prepare("
            INSERT INTO books (title, author_id, category_id, description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['author_id'],
            $data['category_id'],
            $data['description'] ?? ''
        ]);

        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Book added", "id" => $conn->lastInsertId()]);
        break;

    // ── PUT /api/books.php?id=5  →  Update book (admin only) ─────────────────
    case 'PUT':
        require_admin();

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $conn->prepare("
            UPDATE books SET title=?, author_id=?, category_id=?, description=? WHERE id=?
        ");
        $stmt->execute([
            $data['title']       ?? '',
            $data['author_id']   ?? 0,
            $data['category_id'] ?? 0,
            $data['description'] ?? '',
            $id
        ]);

        echo json_encode(["success" => true, "message" => "Book updated"]);
        break;

    // ── DELETE /api/books.php?id=5  →  Delete book (admin only) ──────────────
    case 'DELETE':
        require_admin();

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            break;
        }

        $conn->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
        echo json_encode(["success" => true, "message" => "Book deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>
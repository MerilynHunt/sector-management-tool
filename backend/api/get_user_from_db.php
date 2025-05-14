<?php
session_start();
$allowed_origin = "http://localhost:5173";

header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once __DIR__ . '/../db_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['user' => null]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = $pdo->prepare("SELECT user_name AS username, user_agree_to_terms AS terms FROM users WHERE user_id = :id");
    $sql->execute([':id' => $user_id]);
    $user = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    $sql = $pdo->prepare("SELECT user_sectors_sector_id FROM user_sectors WHERE user_sectors_user_id = :user_id");
    $sql->execute([':user_id' => $user_id]);
    $sectors = $sql->fetchAll(PDO::FETCH_COLUMN);

    $user['sectors'] = array_map('intval', $sectors);

    echo json_encode($user);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
?>
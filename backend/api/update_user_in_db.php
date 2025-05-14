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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

//check for invalid or missing inputs
if (isset($data['username']) && is_string($data['username']) && trim($data['username']) !== '') {
    $clean['username'] = htmlspecialchars(trim($data['username']), ENT_QUOTES, 'UTF-8');
} else {
    $errors[] = 'Invalid or missing username.';
}

$clean['terms'] = isset($data['terms']) ? (bool)$data['terms'] : false;

if(isset($data['sectors']) && is_array($data['sectors']) && count($data['sectors']) > 0) {
  $clean['sectors'] = [];

    foreach ($data['sectors'] as $sector) {
        if (filter_var($sector, FILTER_VALIDATE_INT) !== false) {
            $clean['sectors'][] = (int) $sector;
        } else {
            $errors[] = 'Invalid sector value: ' . $sector;
        }
    }
} else {
    $clean['sectors'] = [];
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit();
}

if ($clean['terms'] === false) { //delete user if doesnt agree to terms anymore
    require_once __DIR__ . '/../db_config.php';

    try {
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        $pdo->prepare("DELETE FROM user_sectors WHERE user_sectors_user_id = :id")->execute([':id' => $user_id]);
        $pdo->prepare("DELETE FROM users WHERE user_id = :id")->execute([':id' => $user_id]);

        $pdo->commit();

        session_unset();
        session_destroy();

        echo json_encode(['success' => true, 'deleted' => true]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error during delete: ' . $e->getMessage()]);
        exit();
    }
}

$username = $clean['username'];
$terms = $clean['terms'];
$sectors = $clean['sectors'];

require_once __DIR__ . '/../db_config.php';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    $sql = $pdo->prepare("UPDATE users SET user_name = :username, user_agree_to_terms = :terms WHERE user_id = :id");
    $sql->execute([
        ':username' => $data['username'],
        ':terms' => (int)$data['terms'],
        ':id' => $user_id,
    ]);

    $pdo->prepare("DELETE FROM user_sectors WHERE user_sectors_user_id = :id")->execute([':id' => $user_id]);

    $sql = $pdo->prepare("INSERT INTO user_sectors (user_sectors_user_id, user_sectors_sector_id) VALUES (:user_id, :sector_id)");
    foreach ($data['sectors'] as $sector_id) {
        $sql->execute([
            ':user_id' => $user_id,
            ':sector_id' => (int)$sector_id,
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'updated' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
?>
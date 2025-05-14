<?php 
$allowed_origin = "http://localhost:5173";

header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

$errors = [];
$clean = [];

//check for invalid or missing inputs
if (isset($data['username']) && is_string($data['username']) && trim($data['username']) !== '') {
    $clean['username'] = htmlspecialchars(trim($data['username']), ENT_QUOTES, 'UTF-8');
} else {
    $errors[] = 'Invalid or missing username.';
}

if (isset($data['terms']) && $data['terms'] === true) {
    $clean['terms'] = true;
} else {
    $errors[] = 'Terms must be accepted to save data.';
}

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
    $errors[] = 'Sectors must be a non empty array.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit();
}

$username = $clean['username'];
$terms = $clean['terms'];
$sectors = $clean['sectors'];

require_once __DIR__ . '/../db_config.php';

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $pdo->beginTransaction();
  
  //users table
  $sql_users = $pdo->prepare("INSERT INTO users (user_name, user_agree_to_terms) VALUES (:user_name, :user_agree_to_terms)");
  $sql_users->bindParam(':user_name', $username);
  $sql_users->bindParam(':user_agree_to_terms', $terms);
  $sql_users->execute();

  //get inserted user id
  $user_id = $pdo->lastInsertId();

  //user_sectors table
  $sql_user_sectors = $pdo->prepare("INSERT INTO user_sectors (user_sectors_user_id, user_sectors_sector_id) VALUES (:user_id, :sector_id)");
    foreach ($sectors as $sector_id) {
        $sql_user_sectors->execute([
            ':user_id' => $user_id,
            ':sector_id' => (int)$sector_id
        ]);
    }

  $pdo->commit();

  echo json_encode(['success' => true, 'user_id' => $user_id]);

} catch(PDOException $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}


$pdo = null; //close connection

?>
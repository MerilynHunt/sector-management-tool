<?php 
$allowed_origin = "http://localhost:5173";

header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once __DIR__ . '/../db_config.php';
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME); //open connection

if ($mysqli -> connect_errno) {
  http_response_code(500);
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

$sql = "SELECT sector_id, sector_name, sector_parent_id FROM sectors";
$result = $mysqli->query($sql);

$sectors = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sanitized_row = [
            'sector_id' => filter_var($row['sector_id'], FILTER_VALIDATE_INT),
            'sector_name' => htmlspecialchars($row['sector_name'], ENT_QUOTES, 'UTF-8'),
            'sector_parent_id' => isset($row['sector_parent_id']) ? filter_var($row['sector_parent_id'], FILTER_VALIDATE_INT) : null,
        ];
        $sectors[] = $sanitized_row;
    }
}

$mysqli->close(); //close connection

echo json_encode($sectors);
?>
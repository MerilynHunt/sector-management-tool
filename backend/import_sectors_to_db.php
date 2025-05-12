<?php 

$mysqli = new mysqli("localhost", "root", "", "sector_management_tool"); //open connection

if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

$html = file_get_contents("sectors.html"); 
$dom = new DOMDocument;
$dom->loadHTML($html);
libxml_clear_errors(); //dont print file errors

$options = $dom->getElementsByTagName("option");
$parents = []; //init stack

foreach ($options as $option) {
  $id = (int)$option->getAttribute("value"); //id
  $text = html_entity_decode($option->nodeValue);

  preg_match_all('/\xC2\xA0/', $text, $nbspMatches);
  $indentLevel = intdiv(count($nbspMatches[0]), 4);

  $text = html_entity_decode($option->nodeValue);
  $name = trim(preg_replace('/\xC2\xA0+/', '', $text)); //name

  $parentId = $parents[$indentLevel - 1] ?? null; //parent id
  $parents[$indentLevel] = $id;
  echo "ID: $id | Name: '$name' | Level: $indentLevel | Parent: " . ($parentId ?? 'NULL') . "<br>";
  
  foreach ($parents as $lvl => $val) { //clean up stack
    if ($lvl > $indentLevel) unset($parents[$lvl]);
  }

  if ($parentId === null) {
    $stmt = $mysqli->prepare("INSERT INTO sectors (sector_id, sector_name) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $name);
  } else {
    $stmt = $mysqli->prepare("INSERT INTO sectors (sector_id, sector_name, sector_parent_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $id, $name, $parentId);
  }
  $stmt->execute();
}

echo "sectors with hierarchy imported successfully";
$mysqli->close(); //close connection
?>
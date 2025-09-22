<?php

// Make connection to the database
function makeConn()
{
  $conn = new mysqli(
    $_ENV["DB_HOST"],
    $_ENV["DB_USER"],
    $_ENV["DB_PASS"],
    $_ENV["DB_NAME"],
    3306
  );
  if ($conn->connect_error) {
    exit("Connection to db failed: " . $conn->connect_error);
  }
  $conn->set_charset("utf8mb4");
  return $conn;
}

// Get data
function sqlQuery($sql, $params) {
  $conn = makeConn();
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(...$params);
  $stmt->execute();
  if (stripos(trim($sql), 'SELECT') === 0) {
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }
}
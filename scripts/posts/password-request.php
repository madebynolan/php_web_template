<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../func.php";
require_once __DIR__ . "/../db-func.php";
require_once __DIR__ . "/../auth-func.php";
require_once __DIR__ . "/../email-func.php";

// Initialize result
$result = [];

function passRequest($userID) {
  // Add token to new password table
  $token = genUUID();
  $newPassQuery = "
    INSERT INTO new_password
    (user_id, token, token_expires_at)
    VALUES (?, ?, NOW() + INTERVAL 30 MINUTE)
  ";
  $newPassParams = [
    "ss",
    $userID,
    $token
  ];
  sqlQuery($newPassQuery, $newPassParams);
  
  // Return token
  return $token;
}

// Check page of request
$page = $_GET["page"];
if ($page === "login") {
  // Check if email
  $email = $_POST["email"];
  if (!$email) {
    $result["status"] === "empty";
    exit(json_encode($result));
  }

  // Validate email
  $result["email"] = validateEmail($email);
  if (!empty($result)) {
    $result["status"] === "fail";
    exit(json_encode($result));
  }

  // Always return success if email is valid
  $userQuery = "
    SELECT *
    FROM users
    WHERE email = ?
  ";
  $userParams = [
    "s",
    $email
  ];
  [$user] = sqlQuery($userQuery, $userParams);
  
  // Send change password email if in users
  if ($user) {
    $user["token"] = passRequest($user["user_id"]);
    sendEmail($user, "changePassword");
  }

  $result["status"] = "success";
  exit(json_encode($result));
} elseif ($page === "profile") {
  // Check user id
  $userID = $_SESSION["userID"];
  if (!$userID) {
    $result["status"] = "fail";
    exit(json_encode($result));
  }

  // Check if user exists
  $userQuery = "
    SELECT *
    FROM users
    WHERE user_id = ?
  ";
  $userParams = [
    "s",
    $userID
  ];
  [$user] = sqlQuery($userQuery, $userParams);
  
  // Return fail if user not found
  if (!$user) {
    $result["status"] = "fail";
    exit(json_encode($result));
  }

  // Send change password email
  $user["token"] = passRequest($user["user_id"]);
  sendEmail($user, "changePassword");
  
  $result["status"] = "success";
  exit(json_encode($result));
}

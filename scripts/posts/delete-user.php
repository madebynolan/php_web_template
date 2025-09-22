<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../db-func.php";
require_once __DIR__ . "/../email-func.php";

// Initialize result
$result = [];

// Get info from post
$password = $_POST["password"];

// Get user info
$userQuery = "
  SELECT *
  FROM users
  WHERE user_id = ?
";
$userParams = [
  "s",
  $_SESSION["userID"]
];
$user = sqlQuery($userQuery, $userParams);

// Error is password incorrect
$match = password_verify($password, $user["password_hash"]);
if (!$match) {
  $result["status"] = "fail";
  exit(json_encode($result));
}

// Delete user data
$deleteUserQuery = "
  DELETE FROM users
  WHERE user_id = ?
";
$deleteUserParams = [
  "s",
  $_SESSION["userID"]
];
sqlQuery($deleteUserQuery, $deleteUserParams);

// Send deleted email
sendEmail($user, "deletedUser");

// Return success
$result["status"] = "success";
exit(json_encode($result));

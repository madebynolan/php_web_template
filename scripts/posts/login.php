<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../func.php";
require_once __DIR__ . "/../auth-func.php";
require_once __DIR__ . "/../db-func.php";
require_once __DIR__ . "/../email-func.php";

// Initialize result
$result = [];

// Get info from post
$info = [
  "emailUsername" => $_POST["emailUsername"], 
  "password"      => $_POST["password"]
];

// Check if no info is provided
if (empty(array_filter($info))) {
  $result["status"] = "empty";
  exit(json_encode($result));
} 

// Check if only 1 provided
if (!$info["emailUsername"] || !$info["password"]) {
  $result["status"] = "missing";
  exit(json_encode($result));
} 

// Get user
$userQuery = "
  SELECT *
  FROM users
  WHERE username = ? OR email = ?
";
$userParamas = [
  "ss", 
  $info["emailUsername"], 
  $info["emailUsername"]
];
[$user] = sqlQuery($userQuery, $userParamas) ?: [[]];
  
// Check if user exists
if (empty($user)) {
  $result["status"] = "fail";
  exit(json_encode($result));
}

// Check if passwords match
$match = password_verify($info["password"], $user["password_hash"]);
$result["status"] = $match ? "success" : "fail";

// Save login attempt
$attemptQuery = "
  INSERT INTO login_attempts
  (user_id, ip_address, success)
  VALUES (?, ?, ?)
";
$attemptParams = [
  "ssi", 
  $user["user_id"],
  $_SERVER["REMOTE_ADDR"],
  $match ? 1 : 0
];
sqlQuery($attemptQuery, $attemptParams);

// Evaluate ban status
if ($result["status"] === "fail") {
  $result["attempts"] = validateBanStatus($user, $_SERVER["REMOTE_ADDR"]);
  $result["status"]   = "fail";
  exit(json_encode($result));
}

// Delete temp bans
$deleteTempQuery = "
  DELETE FROM temp_bans
  WHERE user_id = ?
";
$deleteParams = [
  "s",
  $user["user_id"]
];
sqlQuery($deleteTempQuery, $deleteParams);

// Create successful login
$token = genUUID();

$successQuery = "
  INSERT INTO successful_logins
  (user_id, ip_address, token)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE token = VALUES(token);
";
$successParams = [
  "sss",
  $user["user_id"],
  $_SERVER["REMOTE_ADDR"],
  $token,
];
sqlQuery($successQuery, $successParams);

// Send 2fa code
$code = gen6Digit();
$user["code"]  = $code;
$user["token"] = $token;
sendEmail($user, "2fa");

// Set session variables
$_SESSION["tempID"] = $user["user_id"];
$_SESSION["email"]  = $user["email"];
$_SESSION["code"]   = $code;
$_SESSION["token"]  = $token;
$_SESSION["codeAttempts"] = 0;
$_SESSION["isVerified"] = $user["is_verified"];

// Return success
exit(json_encode($result));
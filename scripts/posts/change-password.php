<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../func.php";
require_once __DIR__ . "/../db-func.php";
require_once __DIR__ . "/../auth-func.php";

// Initialize result
$result = [];

$info = [
  "password"         => $_POST["password"],
  "confirm-password" => $_POST["confirm-password"]
];

// Check if no info provided
if (empty($info)) {
  $result["status"] = "empty";
  exit(json_encode($result));
}

// Validate password
$result["password"] = validatePassword($info["password"]);

// Return if password invalid
if (!empty(array_filter($result)))
  exit(json_encode($result));

// Return if passwords don't match
if ($info["password"] !== $info["confirm-password"]) {
  $result["password"] = "Passwords do not match.";
  exit(json_encode($result));
}

// Return if password is same as old password
$userQuery = "
  SELECT password_hash
  FROM users
  WHERE user_id = ?
";
$userParams = [
  "s",
  $_SESSION["tempUserID"]
];
[$user] = sqlQuery($userQuery, $userParams);

$passQuery = "
  SELECT *
  FROM old_passwords
  WHERE user_id = ?
";
$passParams = [
  "s",
  $_SESSION["tempUserID"]
];
$oldPasswords = sqlQuery($passQuery, $passParams);

foreach ($oldPasswords as $oldPass) {
  if (
    password_verify($info["password"], $user["password_hash"]) ||
    password_verify($info["password"], $oldPass["password_hash"])
    ) {
    $result["password"] = "Password must not match any old passwords.";
    exit(json_encode($result));
  }
}

// Update password
$hash = password_hash($info["password"], PASSWORD_DEFAULT);
$updateQuery = "
  UPDATE users
  SET password_hash = ?
  WHERE user_id = ?
";
$updateParams = [
  "ss",
  $hash,
  $_SESSION["tempUserID"]
];
sqlQuery($updateQuery, $updateParams);

// Store old password
$storeOldQuery = "
  INSERT INTO old_passwords
  (user_id, password_hash)
  VALUES (?, ?)
";
$storeOldParams = [
  "ss",
  $_SESSION["tempUserID"],
  $user["password_hash"]
];
sqlQuery($storeOldQuery, $storeOldParams);

unset($_SESSION["tempUserID"]);

// Return success
$result["status"] = "success";
exit(json_encode($result));

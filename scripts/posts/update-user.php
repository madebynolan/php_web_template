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
  "name"      => $_POST["name"] ?? null, 
  "username"  => $_POST["username"], 
  "email"     => $_POST["email"],
  "location"  => $_POST["location"] ?? null,
  "bio"       => $_POST["bio"] ?? null
];

// Check if no info in require fields
if (empty($info["username"])) 
  $result["username"] = "empty";

if (empty($info["email"])) 
  $result["email"] = "empty";

// Validate name
$result["name"] = validateName($info["name"]);

// Get current user data
$userQuery = "
  SELECT *
  FROM users
  WHERE user_id = ?
";
$userParams = [
  "s",
  $_SESSION["userID"]
];
[$user] = sqlQuery($userQuery, $userParams);
$currentUsername = $user["username"];
$currentEmail    = $user["email"];

// Check if new username taken
if ($info["username"] !== $currentUsername) {
  $takenUserQuery = "
    SELECT username
    FROM users
    WHERE username = ?
  ";
  $takenUserParams = [
    "s",
    $info["username"]
  ];
  $usernameTaken = sqlQuery($takenUserQuery, $takenUserParams);
  if ($usernameTaken)
    $result["username"] = "taken";
}

// Check if new email taken
if ($info["email"] !== $currentEmail) {
  $takenEmailQuery = "
    SELECT email
    FROM users
    WHERE email = ?
  ";
  $takenEmailParams = [
    "s",
    $info["email"]
  ];
  $emailTaken = sqlQuery($takenEmailQuery, $takenEmailParams);
  if ($emailTaken) 
    $result["email"] = "taken";
}

// Return if result not empty
if (!empty(array_filter($result))) {
  $result["status"] = "fail";
  exit(json_encode($result));
}

$result["status"] = "success";

// Update user
$updateUserQuery = "
  UPDATE users
  SET name = ?,
    username = ?,
    email = ?,
    location = ?,
    bio = ?
  WHERE user_id = ?
";
$updateUserParams = [
  "ssssss",
  $info["name"],
  $info["username"],
  $info["email"],
  $info["location"],
  $info["bio"],
  $user["user_id"]
];
sqlQuery($updateUserQuery, $updateUserParams);

if ($info["email"] !== $currentEmail) {
  // Store new email request
  $updateToken = genUUID();
  $revertToken = genUUID();
  $newEmailQuery = "
    INSERT INTO new_email
      (user_id, 
      new_email, 
      old_email,
      revert_token)
    VALUES (?, ?, ?, ?)
  ";
  $newEmailParams = [
    "ssss",
    $user["user_id"],
    $info["email"],
    $user["email"],
    $revertToken
  ];
  sqlQuery($newEmailQuery, $newEmailParams);

  // Change verification
  $unverifyQuery = "
    UPDATE users
    SET is_verified = 0,
      token = ?,
      token_expires_at = NOW() + INTERVAL 60 MINUTE
    WHERE user_id = ?
  ";
  $unverifyParams = [
    "ss",
    $updateToken,
    $user["user_id"]
  ];
  sqlQuery($unverifyQuery, $unverifyParams);

  // Warn original email of change
  $user["token"] = $revertToken;
  sendEmail($user, "emailWarning");

  // Send email to confirm update
  $user["token"] = $updateToken;
  $user["email"] = $info["email"];
  sendEmail($user, "newEmail");
}

exit(json_encode($result));

<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../func.php";
require_once __DIR__ . "/../auth-func.php";
require_once __DIR__ . "/../db-func.php";
require_once __DIR__ . "/../email-func.php";

// Initialize result
$result = [];

// Load reserved usernames
$file = file_get_contents(__DIR__ . "/../../json/reservedUsernames.json");
$reservedUsernames = json_decode($file);

// Get info from post
$info = [
  "username"  => $_POST["username"], 
  "email"     => $_POST["email"], 
  "password"  => $_POST["password"]
];

// Check if no info is provided
if (empty(array_filter($info))) {
  $result["status"] = "empty";
  exit(json_encode($result));
}

// Validate each input
foreach ($info as $key => $value) {
  switch ($key) {
    case "username":
      if ($err = validateUsername($value)) {
        $result[$key] = $err;
      } else {
        $usernameQuery = "
          SELECT *
          FROM users
          WHERE username = ?
        ";
        $usernameParams = [
          "s",
          $username
        ];
        $taken = 
          in_array($username, $reservedUsernames) ||
          sqlQuery($usernameQuery, $usernameParams);
        if ($taken)
          $result[$key] = "Sorry, that username is taken";
      }
      break;
    case "email":
      if ($err = validateEmail($value)) {
        $result[$key] = $err;
      } else {
        $emailQuery = "
          SELECT *
          FROM users
          WHERE email = ?
        ";
        $emailParams = [
          "s",
          $email
        ];
        $taken = sqlQuery($emailQuery, $emailParams);
        if ($taken)
          $result[$key] = "Sorry, that email is already in use.";
      }
      break;
    case "password":
      if ($err = validatePassword($value)) 
        $result[$key] = $err;
      break;
  }
}

// Return failed field results
if (!empty($result)) {
  $result["status"] = "fail";
  exit(json_encode($result));
}

// Generate UUID & token
$info["userID"] = genUUID();
$info["token"]  = genUUID();

// Hash password
$info["passwordHash"] = password_hash($info["password"], PASSWORD_DEFAULT);
unset($info["password"]);

// Add user
$addUserQuery = "
  INSERT INTO users
  (user_id, username, email, password_hash, token, token_expires_at)
  VALUES (?, ?, ?, ?, ?, NOW() + INTERVAL 60 MINUTE)
";
$addUserParams = [
  "sssss",
  $info["userID"],
  $info["username"],
  $info["email"],
  $info["passwordHash"],
  $info["token"]
];
sqlQuery($addUserQuery, $addUserParams);

// Add session variable
$_SESSION["userID"] = $info["userID"];

// Send verification email
$user = [
  "username" => $info["username"],
  "email"    => $info["email"],
  "token"    => $info["token"]
];
sendEmail($user, "verification");

// Add session variables
$_SESSION["userID"] = $info["userID"];
$_SESSION["isVerified"] = 0;

// Return success
$result["status"] = "success";
exit(json_encode($result));
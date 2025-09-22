<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../func.php";
require_once __DIR__ . "/../db-func.php";

// Initialize result
$result = [];

// Get info from post
$info = [
  "code"         => $_POST["code"],
  "staySignedIn" => $_POST["staySignedIn"]
];

// Check if code provided
if (!$info["code"]) {
  $result["code"] = "Please enter the code.";
  exit(json_encode($result));
}

// Track code attempts
$_SESSION["codeAttempts"]++;

// Check if code matches
if ($info["code"] !== $_SESSION["code"]) {
  $result["code"] = "Sorry, that code is incorrect.";
  $result["codeAttempts"] = $_SESSION["codeAttempts"];
  exit(json_encode($result));
}

// Keep user signed in
if ($info["staySignedIn"]) {
  stayLoggedIn();
}

// Adjust session variables
$_SESSION["userID"] = $_SESSION["tempID"];

unset($_SESSION["tempID"]);
unset($_SESSION["username"]);
unset($_SESSION["code"]);
unset($_SESSION["codeAttempts"]);

// Return success
$result["status"] = "success";
exit(json_encode($result));

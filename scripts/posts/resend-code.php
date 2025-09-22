<?php

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../func.php";
require_once __DIR__ . "/../db-func.php";
require_once __DIR__ . "/../email-func.php";

// Initialize result
$result = [];

// Update code
$user = [
  "email" => $_SESSION["email"],
  "code"  => $_SESSION["code"],
  "token" => $_SESSION["token"]
];

// Send new code
sendEmail($user, "2fa");

// Return success
$result["status"] = "success";
exit(json_encode($result));
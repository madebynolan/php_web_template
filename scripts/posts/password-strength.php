<?php

require_once __DIR__ . "/../auth-func.php";

// Calculate stength
$password = $_POST["password"];
exit(json_encode(passwordProgress($password)));
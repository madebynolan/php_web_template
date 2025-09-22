<?php

require_once __DIR__ . "/../vendor/autoload.php";

// Change error logging
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');

// Load environment variables
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

// Set session defaults
$ttl = 60*60*24;
ini_set('session.gc_maxlifetime', $ttl);
session_set_cookie_params(0);
date_default_timezone_set('America/New_York');
session_start();
$_SESSION['__ping'] = !($_SESSION['__ping'] ?? false);

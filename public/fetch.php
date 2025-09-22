<?php

require_once __DIR__ . "/../scripts/init.php";

$page = $_GET["post"];
require_once __DIR__ . "/../scripts/posts/$page.php";

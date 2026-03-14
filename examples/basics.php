<?php

use Andrexm\Astatine\Engine;

include("../vendor/autoload.php");

$engine = Engine::getInstance();
$engine::config(
    __DIR__ . DIRECTORY_SEPARATOR . "views",
    __DIR__ . DIRECTORY_SEPARATOR . "cache"
);

echo $engine::render("home");

echo $engine::$error_message;

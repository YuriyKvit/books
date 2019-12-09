<?php
ini_set('display_errors', 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
require_once 'vendor/autoload.php';

try {
    $app = new \App\Router();
    $app->run();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}
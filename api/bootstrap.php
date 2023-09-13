<?php
use Yohten\Api\Database;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = new Database;
$dbConnection = $db->connect();
<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Import the Dotenv class
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define constants for PayMongo keys
define('PAYMONGO_SECRET_KEY', $_ENV['PAYMONGO_SECRET_KEY']);
define('PAYMONGO_PUBLIC_KEY', $_ENV['PAYMONGO_PUBLIC_KEY']); 
<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index');
    exit();
}

// Update form actions
<form action="profile" method="POST" enctype="multipart/form-data"> 

<link href="src/output.css" rel="stylesheet"> 
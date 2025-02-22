<?php

// After successful login
$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $customer['full_name']; // from customers table
$_SESSION['email'] = $user['email']; 
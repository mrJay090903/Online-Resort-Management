<?php
include 'includes/db_connect.php';

// Create staff table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS staff (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql);

// Add new staff function
function addStaff($staff_name, $contact_number, $email, $password) {
    global $conn;
    
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO staff (staff_name, contact_number, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $staff_name, $contact_number, $email, $hashed_password);
    
    return $stmt->execute();
}

// Get all staff function
function getAllStaff() {
    global $conn;
    
    $sql = "SELECT id, staff_name, contact_number, email, created_at FROM staff";
    $result = $conn->query($sql);
    
    $staff = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }
    }
    return $staff;
}

// Add this new function
function removeStaff($staff_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param("i", $staff_id);
    
    return $stmt->execute();
}

// Add this new function
function updateStaff($staff_id, $staff_name, $contact_number, $email) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE staff SET staff_name = ?, contact_number = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssi", $staff_name, $contact_number, $email, $staff_id);
    
    return $stmt->execute();
}

// Add this to handle staff removal
if (isset($_POST['action']) && $_POST['action'] === 'remove_staff') {
    $staff_id = $_POST['staff_id'];
    if (removeStaff($staff_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to remove staff']);
    }
    exit;
}

// Update the staff update handler
if (isset($_POST['action']) && $_POST['action'] === 'update_staff') {
    header('Content-Type: application/json');
    
    try {
        $staff_id = $_POST['staff_id'];
        $staff_name = $_POST['staff_name'];
        $contact_number = $_POST['contact_number'];
        $email = $_POST['email'];
        
        if (updateStaff($staff_id, $staff_name, $contact_number, $email)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
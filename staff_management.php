<?php
require_once 'includes/functions/staff_functions.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_name = $_POST['staff_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $result = addStaff($staff_name, $contact_number, $email, $password);
    
    if ($result['success']) {
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Staff added successfully!',
                icon: 'success',
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        </script>";
    } else {
        $validation_errors = $result['errors'];
        echo "<script>
            Swal.fire({
                title: 'Validation Error',
                html: '" . implode('<br>', array_values($validation_errors)) . "',
                icon: 'error'
            });
        </script>";
    }
}

// Get all staff
$staff_list = getAllStaff();
?>
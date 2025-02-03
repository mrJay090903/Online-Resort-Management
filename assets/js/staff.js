async function removeStaff(staffId) {
    // Show confirmation dialog
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await fetch('staff_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove_staff&staff_id=${staffId}`
        });

        const data = await response.json();

        if (data.success) {
            // Remove the row and close modal
            document.querySelector(`tr[data-staff-id="${staffId}"]`).remove();
            document.getElementById('viewStaffModal').classList.add('hidden');

            // Show success message
            await Swal.fire({
                title: 'Deleted!',
                text: 'Staff has been removed successfully.',
                icon: 'success',
                timer: 1500
            });
        } else {
            // Show error message
            await Swal.fire({
                title: 'Error!',
                text: 'Failed to remove staff.',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            title: 'Error!',
            text: 'An error occurred while removing staff.',
            icon: 'error'
        });
    }
}

function openViewModal(staffId) {
    // Find staff data from the table
    const staffRow = document.querySelector(`tr[data-staff-id="${staffId}"]`);
    const staffName = staffRow.querySelector('[data-field="name"]').textContent;
    const staffContact = staffRow.querySelector('[data-field="contact"]').textContent;
    const staffEmail = staffRow.querySelector('[data-field="email"]').textContent;
    
    // Fill the modal with data
    document.getElementById('view-staff-name').value = staffName;
    document.getElementById('view-contact-number').value = staffContact;
    document.getElementById('view-email').value = staffEmail;
    
    // Store the staff ID in the form
    document.getElementById('view-staff-id').value = staffId;
    
    // Start in edit mode
    const inputs = ['view-staff-name', 'view-contact-number', 'view-email'];
    inputs.forEach(id => {
        const input = document.getElementById(id);
        input.readOnly = false;
        input.classList.remove('bg-gray-100');
        input.classList.add('bg-white');
    });
    
    // Show the modal
    document.getElementById('viewStaffModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewStaffModal').classList.add('hidden');
}

function validateStaffForm(formData) {
    const errors = {};
    
    // Validate staff name
    const staffName = formData.get('staff_name');
    if (!staffName || staffName.length < 2 || !/^[a-zA-Z ]*$/.test(staffName)) {
        errors.staff_name = "Name must contain only letters and be at least 2 characters long";
    }
    
    // Validate contact number (must start with 09 and have 11 digits total)
    const contactNumber = formData.get('contact_number');
    if (!contactNumber || !/^09[0-9]{9}$/.test(contactNumber)) {
        errors.contact_number = "Contact number must start with 09 and be 11 digits long";
    }
    
    // Validate email
    const email = formData.get('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
        errors.email = "Please enter a valid email address";
    }
    
    // Validate password if present
    const password = formData.get('password');
    if (password !== null && password.length > 0) {
        if (password.length < 8 || !/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) {
            errors.password = "Password must be at least 8 characters long and contain both letters and numbers";
        }
    }
    
    return errors;
}

async function updateStaffDetails() {
    const formData = new FormData(document.getElementById('viewStaffForm'));
    
    // Validate form
    const errors = validateStaffForm(formData);
    if (Object.keys(errors).length > 0) {
        let errorMessage = '<ul class="text-left">';
        Object.values(errors).forEach(error => {
            errorMessage += `<li>• ${error}</li>`;
        });
        errorMessage += '</ul>';
        
        await Swal.fire({
            title: 'Validation Error',
            html: errorMessage,
            icon: 'error'
        });
        return;
    }
    
    const staffId = document.getElementById('view-staff-id').value;
    const staffName = document.getElementById('view-staff-name').value;
    const contactNumber = document.getElementById('view-contact-number').value;
    const email = document.getElementById('view-email').value;

    try {
        const response = await fetch('staff_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_staff&staff_id=${staffId}&staff_name=${encodeURIComponent(staffName)}&contact_number=${encodeURIComponent(contactNumber)}&email=${encodeURIComponent(email)}`
        });

        const data = await response.json();

        if (data.success) {
            // Update the table row
            const row = document.querySelector(`tr[data-staff-id="${staffId}"]`);
            row.querySelector('[data-field="name"]').textContent = staffName;
            row.querySelector('[data-field="contact"]').textContent = contactNumber;
            row.querySelector('[data-field="email"]').textContent = email;

            // Show success message
            await Swal.fire({
                title: 'Updated!',
                text: 'Staff details have been updated successfully.',
                icon: 'success',
                timer: 1500
            });

            // Close the modal
            document.getElementById('viewStaffModal').classList.add('hidden');
        } else {
            await Swal.fire({
                title: 'Error!',
                text: 'Failed to update staff details.',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            title: 'Error!',
            text: 'An error occurred while updating staff details.',
            icon: 'error'
        });
    }
}

// Add these functions for real-time validation
function showInputError(input, message) {
    const errorDiv = input.nextElementSibling;
    input.classList.add('border-red-500');
    
    if (!errorDiv || !errorDiv.classList.contains('error-message')) {
        const div = document.createElement('p');
        div.className = 'error-message text-red-500 text-xs mt-1';
        div.textContent = message;
        input.parentNode.insertBefore(div, input.nextSibling);
    } else {
        errorDiv.textContent = message;
    }
}

function clearInputError(input) {
    const errorDiv = input.nextElementSibling;
    input.classList.remove('border-red-500');
    
    if (errorDiv && errorDiv.classList.contains('error-message')) {
        errorDiv.remove();
    }
}

function validateInput(input) {
    const value = input.value;
    const name = input.name;
    let error = null;

    switch (name) {
        case 'staff_name':
            if (!value || value.length < 2 || !/^[a-zA-Z ]*$/.test(value)) {
                error = "Name must contain only letters and be at least 2 characters long";
            }
            break;

        case 'contact_number':
            if (!value || !/^09[0-9]{9}$/.test(value)) {
                error = "Contact number must start with 09 and be 11 digits long";
            }
            break;

        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!value || !emailRegex.test(value)) {
                error = "Please enter a valid email address";
            }
            break;

        case 'password':
            if (value.length > 0 && (value.length < 8 || !/[A-Za-z]/.test(value) || !/[0-9]/.test(value))) {
                error = "Password must be at least 8 characters long and contain both letters and numbers";
            }
            break;
    }

    if (error) {
        showInputError(input, error);
        return false;
    } else {
        clearInputError(input);
        return true;
    }
}

// Add this to initialize real-time validation
function initializeRealTimeValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', () => validateInput(input));
            input.addEventListener('blur', () => validateInput(input));
        });
    });
}

// Update the validateAddStaffForm function
async function validateAddStaffForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const inputs = form.querySelectorAll('input');
    let hasErrors = false;
    
    inputs.forEach(input => {
        if (!validateInput(input)) {
            hasErrors = true;
        }
    });
    
    if (hasErrors) {
        await Swal.fire({
            title: 'Validation Error',
            text: 'Please correct the errors in the form',
            icon: 'error'
        });
        return;
    }
    
    // If validation passes, submit the form
    form.submit();
}

// Add this to initialize when the page loads
document.addEventListener('DOMContentLoaded', initializeRealTimeValidation); 
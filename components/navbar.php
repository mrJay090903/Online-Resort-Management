<!-- Navbar -->
<nav class="bg-white shadow-lg fixed w-full z-50">
  <div class="max-w-full px-4">
    <div class="flex justify-between items-center h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <img src="assets/casitalogo-removebg-preview.png" alt="Logo" class="h-12 w-auto">
      </div>

      <!-- Navigation Links - Centered -->
      <div class="hidden md:flex items-center justify-center flex-1">
        <div class="flex space-x-8">
          <a href="index.php"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Home
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="#"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Rooms
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="#"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Reservations
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="index.php#features"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Features
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="about-us.php"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            About us
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>
        </div>
      </div>

      <!-- User Profile/Login Button -->
      <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none">
            <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <div class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center text-white">
              <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
              fill="currentColor">
              <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <?php if ($_SESSION['user_type'] === 'admin'): ?>
            <a href="admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <?php elseif ($_SESSION['user_type'] === 'customer'): ?>
            <a href="customer/customer_dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-user mr-2"></i> My Account
            </a>
            <?php endif; ?>
            <hr class="my-1">
            <a href="handlers/logout_handler.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
              <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
          </div>
        </div>
        <?php else: ?>
        <button onclick="toggleModal()"
          class="bg-emerald-500 text-white px-4 py-2 rounded-md hover:bg-emerald-600 transition duration-300">
          Login
        </button>
        <?php endif; ?>
      </div>

      <!-- Mobile menu button -->
      <div class="md:hidden flex items-center">
        <button class="mobile-menu-button">
          <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu hidden md:hidden">
      <a href="#" class="block py-2 px-4 text-sm hover:bg-gray-200">Home</a>
      <a href="#about" class="block py-2 px-4 text-sm hover:bg-gray-200">About</a>
      <a href="#features" class="block py-2 px-4 text-sm hover:bg-gray-200">Features</a>
      <a href="#contact" class="block py-2 px-4 text-sm hover:bg-gray-200">Contact</a>
    </div>
  </div>
</nav>

<!-- Login Modal -->
<div id="loginModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
  <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

  <div class="relative bg-white/90 backdrop-blur-sm p-6 rounded-lg shadow-lg w-96">
    <button onclick="toggleModal()"
      class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
    <h2 class="text-2xl font-bold text-center mb-4">Sign In</h2>

    <form class="flex flex-col items-center" action="index.php" method="POST">
      <!-- Email -->
      <div class="mb-4 w-80 mx-auto">
        <label for="email" class="block text-sm text-gray-600">Email</label>
        <input type="email" id="email" name="email"
          class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
          required placeholder="Enter your email">
      </div>

      <!-- Password -->
      <div class="mb-4 w-80 mx-auto relative">
        <label for="password" class="block text-sm text-gray-600">Password</label>
        <input type="password" id="password" name="password"
          class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
          required placeholder="Enter your password">
        <!-- Show Password -->
        <button type="button" onclick="togglePassword('password','eyeIcon', event)"
          class="absolute inset-y-0 right-3 flex items-center pt-4"></button>
      </div>

      <!-- Forgot Password -->
      <p class="text-sm text-[#00B58B] font-semibold hover:underline cursor-pointer mb-4 w-80 mx-auto text-left">
        <a href="#" onclick="toggleForgotpass()">Forgot your password?</a>
      </p>

      <button type="submit" name="login"
        class="w-40 px-4 py-2 bg-[#242424] text-white transition-transform transform hover:scale-105 hover:bg-gray-700">
        SIGN IN
      </button>
      <p class="mt-4 text-sm">Don't have an account? <span onclick="toggleSignupModal()"
          class="text-[#00B58B] cursor-pointer font-semibold hover:underline">Sign up</span></p>
    </form>
  </div>
</div>

<!-- Signup Modal -->
<div id="signupModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
  <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

  <div
    class="relative bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-300">
    <div class="absolute top-4 right-4">
      <button onclick="toggleSignupModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <div class="p-8">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
        <p class="text-gray-600">Join our community today</p>
      </div>

      <form class="space-y-6" action="index.php" method="POST">
        <!-- Full Name -->
        <div>
          <label for="fullName" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <input type="text" id="fullName" name="full_name" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                          focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"
              required placeholder="John Doe" pattern="[A-Za-z\s]+"
              title="Please enter a valid name (letters and spaces only)">
          </div>
        </div>

        <!-- Contact Number -->
        <div>
          <label for="contactNumber" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
            </div>
            <input type="tel" id="contactNumber" name="contact_number" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                          focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"
              required placeholder="+63 XXX XXX XXXX" pattern="[0-9]+" title="Please enter a valid phone number">
          </div>
        </div>

        <!-- Email -->
        <div>
          <label for="signupEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
            </div>
            <input type="email" id="signupEmail" name="email" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                          focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"
              required placeholder="you@example.com">
          </div>
        </div>

        <!-- Password -->
        <div>
          <label for="signupPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <input type="password" id="signupPassword" name="password" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                          focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"
              required placeholder="••••••••">
          </div>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <input type="password" id="confirmPassword" name="confirm_password" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                          focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"
              required placeholder="••••••••">
          </div>
        </div>

        <button type="submit" name="signup" class="w-full py-3 px-4 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 
                       transition-all duration-300 transform hover:scale-[1.02]">
          Create Account
        </button>

        <p class="text-center text-sm text-gray-600">
          Already have an account?
          <span onclick="switchToLogin()"
            class="text-emerald-600 font-semibold cursor-pointer hover:text-emerald-700 hover:underline">
            Sign in
          </span>
        </p>
      </form>
    </div>
  </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotPasswordModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
  <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

  <div class="relative bg-white/90 backdrop-blur-sm p-6 rounded-lg w-96">
    <button onclick="toggleForgotpass()"
      class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
    <h2 class="text-2xl font-bold text-center mb-4">Forgot Password</h2>

    <!-- Email Form -->
    <form id="forgotPasswordForm" action="handlers/forgot_password_handler.php" method="POST" class="space-y-6">
      <div>
        <label for="resetEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
            </svg>
          </div>
          <input type="email" id="resetEmail" name="email" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                        focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"
            required placeholder="Enter your email address">
        </div>
      </div>

      <button type="submit" id="sendCodeBtn" class="w-full py-3 px-4 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 
                     focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 
                     transition-all duration-300 transform hover:scale-[1.02]">
        Send Reset Code
      </button>
    </form>

    <!-- Reset Code Form (Initially Hidden) -->
    <form id="resetCodeForm" action="handlers/reset_password_handler.php" method="POST" class="hidden space-y-6"
      onsubmit="return validatePasswords()">
      <!-- Timer Display -->
      <div class="text-center mb-4">
        <p class="text-sm text-gray-600">Code expires in:</p>
        <p id="codeTimer" class="text-lg font-semibold text-emerald-600">60:00</p>
      </div>

      <div>
        <label for="resetCode" class="block text-sm font-medium text-gray-700 mb-1">Reset Code</label>
        <input type="text" id="resetCode" name="reset_code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                      focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required
          placeholder="Enter the 6-digit code">
      </div>

      <div>
        <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
        <div class="relative">
          <input type="password" id="newPassword" name="new_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                        focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required
            placeholder="Enter new password" oninput="validatePasswordMatch()">
          <span id="passwordStrength" class="text-xs text-gray-500 mt-1 block"></span>
        </div>
      </div>

      <div>
        <label for="confirmNewPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New
          Password</label>
        <div class="relative">
          <input type="password" id="confirmNewPassword" name="confirm_new_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 
                        focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required
            placeholder="Confirm new password" oninput="validatePasswordMatch()">
          <span id="passwordMatch" class="text-xs mt-1 block"></span>
        </div>
      </div>

      <button type="submit" id="resetPasswordBtn" class="w-full py-3 px-4 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 
                     focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 
                     transition-all duration-300">
        Reset Password
      </button>

      <p class="text-center text-sm text-gray-600 mt-4">
        Didn't receive the code?
        <button type="button" onclick="showEmailForm()"
          class="text-emerald-600 font-semibold hover:text-emerald-700 hover:underline">
          Try again
        </button>
      </p>
    </form>
  </div>
</div>

<script>
// Modal Toggle Functions
function toggleModal() {
  document.getElementById('loginModal').classList.toggle('hidden');
}

function toggleSignupModal() {
  document.getElementById('signupModal').classList.toggle('hidden');
  document.getElementById('loginModal').classList.add('hidden');
}

function toggleForgotpass() {
  const modal = document.getElementById('forgotPasswordModal');
  const emailForm = document.getElementById('forgotPasswordForm');
  const resetForm = document.getElementById('resetCodeForm');

  if (modal.classList.contains('hidden')) {
    modal.classList.remove('hidden');
    emailForm.classList.remove('hidden');
    resetForm.classList.add('hidden');
  } else {
    modal.classList.add('hidden');
    clearInterval(timerInterval); // Clear timer when modal is closed
  }
}

function switchToLogin() {
  toggleSignupModal();
  toggleModal();
}

function togglePassword(inputId, eyeIconId, event) {
  if (event) {
    event.preventDefault();
  }
  const passwordInput = document.getElementById(inputId);
  const eyeIcon = document.getElementById(eyeIconId);

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    eyeIcon.src = "components/eye.png";
  } else {
    passwordInput.type = "password";
    eyeIcon.src = "components/hidden.png";
  }
}

function toggleMobileMenu() {
  const mobileMenu = document.getElementById('mobileMenu');
  mobileMenu.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
  const forgotPasswordForm = document.getElementById('forgotPasswordForm');

  forgotPasswordForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
      const formData = new FormData(this);
      const response = await fetch(this.action, {
        method: 'POST',
        body: formData
      });

      const result = await response.text();

      // Show success message and switch to reset code form
      Swal.fire({
        icon: 'success',
        title: 'Code Sent!',
        text: 'Please check your email for the reset code.',
        confirmButtonColor: '#059669'
      }).then(() => {
        showResetCodeForm();
      });

    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Something went wrong! Please try again.',
        confirmButtonColor: '#059669'
      });
    }
  });
});

let timerInterval;

function startTimer(duration) {
  let timer = duration;
  const timerDisplay = document.getElementById('codeTimer');

  clearInterval(timerInterval); // Clear any existing timer

  timerInterval = setInterval(function() {
    const minutes = parseInt(timer / 60, 10);
    const seconds = parseInt(timer % 60, 10);

    timerDisplay.textContent = minutes.toString().padStart(2, '0') + ':' +
      seconds.toString().padStart(2, '0');

    if (--timer < 0) {
      clearInterval(timerInterval);
      Swal.fire({
        icon: 'error',
        title: 'Code Expired',
        text: 'The reset code has expired. Please request a new one.',
        confirmButtonColor: '#059669'
      }).then(() => {
        showEmailForm();
      });
    }
  }, 1000);
}

function showResetCodeForm() {
  document.getElementById('forgotPasswordForm').classList.add('hidden');
  document.getElementById('resetCodeForm').classList.remove('hidden');
  startTimer(3600); // Start 1-hour countdown (3600 seconds)
}

function showEmailForm() {
  document.getElementById('resetCodeForm').classList.add('hidden');
  document.getElementById('forgotPasswordForm').classList.remove('hidden');
  clearInterval(timerInterval); // Clear timer when switching back to email form
}

function validatePasswordMatch() {
  const password = document.getElementById('newPassword').value;
  const confirmPassword = document.getElementById('confirmNewPassword').value;
  const matchDisplay = document.getElementById('passwordMatch');
  const resetButton = document.getElementById('resetPasswordBtn');

  if (confirmPassword) {
    if (password === confirmPassword) {
      matchDisplay.textContent = 'Passwords match';
      matchDisplay.className = 'text-xs text-emerald-600 mt-1 block';
      resetButton.disabled = false;
      resetButton.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
      matchDisplay.textContent = 'Passwords do not match';
      matchDisplay.className = 'text-xs text-red-600 mt-1 block';
      resetButton.disabled = true;
      resetButton.classList.add('opacity-50', 'cursor-not-allowed');
    }
  } else {
    matchDisplay.textContent = '';
    resetButton.disabled = false;
    resetButton.classList.remove('opacity-50', 'cursor-not-allowed');
  }
}

function validatePasswords() {
  const password = document.getElementById('newPassword').value;
  const confirmPassword = document.getElementById('confirmNewPassword').value;

  if (password !== confirmPassword) {
    Swal.fire({
      icon: 'error',
      title: 'Password Mismatch',
      text: 'The passwords you entered do not match. Please try again.',
      confirmButtonColor: '#059669'
    });
    return false;
  }

  if (password.length < 8) {
    Swal.fire({
      icon: 'error',
      title: 'Password Too Short',
      text: 'Password must be at least 8 characters long.',
      confirmButtonColor: '#059669'
    });
    return false;
  }

  return true;
}

// Add password strength indicator
document.getElementById('newPassword').addEventListener('input', function() {
  const password = this.value;
  const strengthDisplay = document.getElementById('passwordStrength');

  // Check password strength
  let strength = 0;
  if (password.length >= 8) strength++;
  if (password.match(/[a-z]/)) strength++;
  if (password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;

  // Update strength indicator
  switch (strength) {
    case 0:
    case 1:
      strengthDisplay.textContent = 'Weak password';
      strengthDisplay.className = 'text-xs text-red-600 mt-1 block';
      break;
    case 2:
    case 3:
      strengthDisplay.textContent = 'Medium strength password';
      strengthDisplay.className = 'text-xs text-yellow-600 mt-1 block';
      break;
    case 4:
    case 5:
      strengthDisplay.textContent = 'Strong password';
      strengthDisplay.className = 'text-xs text-emerald-600 mt-1 block';
      break;
  }
});
</script>
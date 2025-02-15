<?php
// Start session at the very beginning of the file, before any output
session_start();
require_once 'config/database.php';

// Initialize error/success messages if not set
if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
}
if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = '';
}

// Handle Login
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Debug log
    error_log("Login attempt - Email: " . $email . ", Password: " . $password);
    
    // Query to check user credentials
    $sql = "SELECT u.id, u.email, u.password, u.user_type,
            CASE 
                WHEN u.user_type = 'customer' THEN c.full_name
                WHEN u.user_type = 'staff' THEN s.staff_name
                WHEN u.user_type = 'admin' THEN u.email
            END as name
            FROM users u
            LEFT JOIN customers c ON u.id = c.user_id AND u.user_type = 'customer'
            LEFT JOIN staff s ON u.id = s.user_id AND u.user_type = 'staff'
            WHERE u.email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // For testing: If it's admin and password is admin123, allow login
            if ($user['user_type'] === 'admin' && $password === 'admin123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                header('Location: admin/dashboard.php');
                exit();
            }
            // For regular users, use password verification
            else if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                switch($_SESSION['user_type']) {
                    case 'customer':
                        // Get customer details
                        $customer_sql = "SELECT * FROM customers WHERE user_id = ?";
                        $stmt = $conn->prepare($customer_sql);
                        $stmt->bind_param("i", $user['id']);
                        $stmt->execute();
                        $customer_result = $stmt->get_result();
                        if ($customer_data = $customer_result->fetch_assoc()) {
                            $_SESSION['customer_id'] = $customer_data['id'];
                            $_SESSION['full_name'] = $customer_data['full_name'];
                            $_SESSION['contact_number'] = $customer_data['contact_number'];
                        }
                        header('Location: customer/customer_dashboard.php');
                        exit();
                    case 'staff':
                        header('Location: staff/staff_dashboard.php');
                        exit();
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        exit();
                }
            } else {
                $_SESSION['error'] = "Invalid password";
            }
        } else {
            $_SESSION['error'] = "User not found";
        }
        $stmt->close();
    } else {
        error_log("SQL preparation failed: " . $conn->error);
        $_SESSION['error'] = "Database error occurred";
    }
}

// Handle Signup
if (isset($_POST['signup'])) {
    $conn->begin_transaction();
    
    try {
        $name = $conn->real_escape_string($_POST['fullName']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];
        $contactNumber = $conn->real_escape_string($_POST['contactNumber']);
        
        // Validate password match
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }
        
        // Check if email already exists
        $checkEmail = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($checkEmail)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception("Email already exists");
            }
            $stmt->close();
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into users table
        $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'customer')";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $email, $hashedPassword);
            $stmt->execute();
            $userId = $conn->insert_id;
            $stmt->close();
            
            // Insert into customers table
            $sql = "INSERT INTO customers (user_id, full_name, contact_number, email) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("isss", $userId, $name, $contactNumber, $email);
                $stmt->execute();
                $stmt->close();
                
                $conn->commit();
                $_SESSION['success'] = "Registration successful! Please login.";
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Casita De Grands</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>



  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Sans+Georgian:wght@100..900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/second-quotes" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu+Sans:ital,wght@0,100..800;1,100..800&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ephesis&display=swap" rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/winter-story" rel="stylesheet">
  <!-- Add SweetAlert2 CSS and JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans">

  <?php
  // Handle alerts using SweetAlert2
  if (!empty($_SESSION['error'])): ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: <?php echo json_encode($_SESSION['error']); ?>,
      confirmButtonColor: '#242424'
    });
  });
  </script>
  <?php 
    unset($_SESSION['error']);
  endif; 

  if (!empty($_SESSION['success'])): ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: <?php echo json_encode($_SESSION['success']); ?>,
      confirmButtonColor: '#242424'
    });
  });
  </script>
  <?php 
    unset($_SESSION['success']);
  endif; 
  ?>

  <!-- Navigation -->
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
              x-transition:enter-end="transform opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-75"
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
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
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

  <!-- Add margin-top to account for fixed navbar -->
  <div class="pt-16">

    <!-- Login Modal -->
    <div id="loginModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-96 relative">
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
              class="absolute inset-y-0 right-3 flex items-center pt-4">

            </button>
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

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
      <div class="shadow-lg bg-white p-6 rounded-lg w-96 relative">
        <button onclick="toggleForgotpass()"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
        <h2 class="text-2xl font-bold text-center mb-4">Forgot Password</h2>
        <!-- Add your forgot password form here -->
      </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
      <div class="relative bg-white p-6 rounded-lg shadow-xl w-96">
        <button onclick="toggleSignupModal()"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
        <h2 class="text-2xl font-bold font-['Noto_Sans_Georgian'] mb-4 text-center">Sign Up</h2>

        <form class="flex flex-col items-center" action="index.php" method="POST">
          <!-- Full Name -->
          <div class="mb-4 w-84 mx-auto mt-4">
            <input type="text" id="fullName" name="fullName"
              class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
              required placeholder="Full Name" pattern="[A-Za-z\s]+"
              title="Please enter a valid name (letters and spaces only)">
          </div>

          <!-- Contact Number -->
          <div class="mb-4 w-84 mx-auto">
            <input type="tel" id="contactNumber" name="contactNumber"
              class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
              required placeholder="Contact Number" pattern="[0-9]+" title="Please enter a valid phone number">
          </div>

          <!-- Email -->
          <div class="mb-4 w-84 mx-auto">
            <input type="email" id="signupEmail" name="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
              required placeholder="Email">
          </div>

          <!-- Password -->
          <div class="mb-4 w-84 mx-auto">
            <input type="password" id="signupPassword" name="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
              required placeholder="Password">
          </div>

          <!-- Confirm Password -->
          <div class="mb-6 w-84 mx-auto">
            <input type="password" id="confirmPassword" name="confirmPassword"
              class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
              required placeholder="Confirm Password">
          </div>

          <!-- Submit button -->
          <button type="submit" name="signup"
            class="w-40 px-4 py-2 bg-[#242424] text-white transition-transform transform hover:scale-105 hover:bg-gray-700">
            SIGN UP
          </button>
          <p class="mt-4 text-sm">Already have an account? <span onclick="switchToLogin()"
              class="text-[#00B58B] cursor-pointer font-semibold hover:underline">Sign in</span></p>
        </form>
      </div>
    </div>

    <!-- Hero Section -->
    <section class="relative w-full h-[570px] overflow-hidden">
      <video class="absolute inset-0 w-full h-full object-cover brightness-85" autoplay loop muted>
        <source src="videos/bg-vid.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>

      <div class="absolute inset-0 flex flex-col justify-center items-center text-white text-center px-4">
        <p class="font-['Raleway'] text-sm uppercase tracking-widest">Welcome To</p>
        <h1 class="text-6xl font-bold font-['playfair']">CASITA DE GRANDS</h1>
        <p class="mt-2 text-lg">Escape to Tranquility, Your Hidden Paradise Awaits</p>
        <button
          class="w-40 mt-6 px-4 py-2 border-2 border-white text-white bg-transparent bg-opacity-40 backdrop-blur-md hover:bg-white hover:text-black transition-all duration-300">
          Stay with Us
        </button>
      </div>
    </section>

    <!-- Description Section -->
    <section class="text-center py-25 px-6 mt-2">
      <h2 class="text-4xl font-['Second_Quotes']">Your Escape to Serenity</h2>
      <p class="mt-10 text-gray-600 max-w-3xl mx-auto font-['Ubuntu_Sans']">
        Looking for a relaxing escape? Casita De Grands, hidden away in the lush greenery of Muladbucad Grande,
        Guinobatan, Albay, is the perfect place to unwind.
        Just minutes from Guinobatan Centro, our resort offers a peaceful retreat surrounded by nature. Take a dip in
        our
        beautiful infinity pool and immerse yourself
        in the calming view of lush nature all around.
      </p>
      <p class="mt-2 text-gray-600">
        We're located at Purok 7, Muladbucad Grande, Guinobatan, Albay. Come and make unforgettable memories with us!
      </p>
    </section>

    <!-- Carousel Section -->
    <section class="text-center py-50 px-6 mt-2 mb-10">
      <div class="relative">
        <h2 class="text-center mb-4 text-4xl font-['Ephesis']">Discover Your Perfect Stay</h2>
        <!-- Carousel Container -->
        <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
          <img src="assets/image1.jpg" alt="Image 1" class="w-full flex-shrink-0">
          <img src="assets/image2.jpg" alt="Image 2" class="w-full flex-shrink-0">
          <img src="assets/image3.jpg" alt="Image 3" class="w-full flex-shrink-0">
        </div>

        <!-- Left Button -->
        <button onclick="prevSlide()"
          class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full">
          &#10094;
        </button>

        <!-- Right Button -->
        <button onclick="nextSlide()"
          class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full">
          &#10095;
        </button>
      </div>
    </section>

    <script>
    let currentIndex = 0;
    const carousel = document.getElementById('carousel');
    const totalSlides = carousel.children.length;

    function updateCarousel() {
      const translateX = -currentIndex * 100;
      carousel.style.transform = `translateX(${translateX}%)`;
    }

    function nextSlide() {
      if (currentIndex < totalSlides - 1) {
        currentIndex++;
      } else {
        currentIndex = 0;
      }
      updateCarousel();
    }

    function prevSlide() {
      if (currentIndex > 0) {
        currentIndex--;
      } else {
        currentIndex = totalSlides - 1;
      }
      updateCarousel();
    }
    </script>

    <!-- Feature Section -->
    <section id="features" class="bg-gray-100 py-30">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold text-gray-800 mb-6">Our Features</h2>
        <p class="text-gray-600 mb-12 text-lg">Discover the luxurious amenities and breathtaking experiences at Casita
          De
          Grands.</p>

        <div class="grid md:grid-cols-3 gap-12">
          <!-- Feature 1 -->
          <div class="bg-white p-6 shadow-lg rounded-lg hover:shadow-xl transition">
            <img src="assets/infinity-pool.png" alt="Infinity Pool" class="w-16 h-16 mx-auto mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Infinity Pool</h3>
            <p class="text-gray-600 mt-2">Enjoy a refreshing swim with a stunning view of nature.</p>
          </div>

          <!-- Feature 2 -->
          <div class="bg-white p-6 shadow-lg rounded-lg hover:shadow-xl transition">
            <img src="assets/cottage.png" alt="Cozy Cottages" class="w-16 h-16 mx-auto mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Cozy Cottages</h3>
            <p class="text-gray-600 mt-2">Relax in our well-designed cottages surrounded by lush greenery.</p>
          </div>

          <!-- Feature 3 -->
          <div class="bg-white p-6 shadow-lg rounded-lg hover:shadow-xl transition">
            <img src="assets/nature.png" alt="Nature Escape" class="w-16 h-16 mx-auto mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Nature Escape</h3>
            <p class="text-gray-600 mt-2">Experience tranquility and reconnect with nature.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Customer Feedback Section -->
    <section class="bg-white py-24">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-['Second_Quotes'] text-gray-800 mb-2">Customer's Feedback</h2>
        <p class="text-gray-500 uppercase text-xs tracking-[0.5em] mb-12 font-['Raleway']">Your Opinion Matters</p>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Feedback 1 -->
          <div class="bg-gray-100 p-8 rounded-lg shadow-md">
            <img src="assets/jay-ar.png" alt="Jay-Ar C." class="w-16 h-16 mx-auto rounded-full mb-4">
            <p class="italic text-gray-600">
              "An absolutely magical experience. The attention to detail and personalized service exceeded all our
              expectations. The Casita was a paradise within paradise."
            </p>
            <h3 class="mt-4 font-semibold text-gray-800">Jay-Ar C.</h3>
          </div>

          <!-- Feedback 2 -->
          <div class="bg-gray-100 p-8 rounded-lg shadow-md">
            <img src="assets/michael.png" alt="Michael John D." class="w-16 h-16 mx-auto rounded-full mb-4">
            <p class="italic text-gray-600">
              "The culinary experience was outstanding. Each meal was a journey through flavors, and the private dining
              setup in our La Villa Grande made every evening special."
            </p>
            <h3 class="mt-4 font-semibold text-gray-800">Michael John D.</h3>
          </div>

          <!-- Feedback 3 -->
          <div class="bg-gray-100 p-8 rounded-lg shadow-md">
            <img src="assets/jonathan.png" alt="Jonathan B." class="w-16 h-16 mx-auto rounded-full mb-4">
            <p class="italic text-gray-600">
              "The exceptional customer service and delicious catering exceeded all expectations. Paired with the
              stunning
              infinity pool and the serene environment, it truly became the perfect escape from city life."
            </p>
            <h3 class="mt-4 font-semibold text-gray-800">Jonathan B.</h3>
          </div>
        </div>
      </div>
    </section>

    <!-- Booknow Section -->
    <section class="bg-gray-900 text-white py-35 text-center">
      <h2 class="text-4xl font-cursive mb-4 font-['Winter_Story']">Begin Your Journey</h2>
      <p class="text-gray-400 mb-6">Experience luxury beyond imagination at Casita De Grands</p>
      <a href="#booking"
        class="border border-white text-white px-6 py-3 inline-block hover:bg-white hover:text-gray-900 transition">Book
        Your Stay</a>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-12">
      <div class="container mx-auto grid md:grid-cols-4 gap-8 text-center md:text-left px-6">
        <!-- Email -->
        <div>
          <p class="font-semibold mb-2">EMAIL</p>
          <a href="mailto:casitadegrands@gmail.com" class="flex items-center justify-center md:justify-start space-x-2">
            <img src="../assets/email.svg" alt="Email Icon" class="w-4 h-4">
            <span class="font-semibold text-sm">casitadegrands@gmail.com</span>
          </a>
        </div>
        <!-- Location -->
        <div>
          <p class="font-semibold mb-2">LOCATION</p>
          <a href="https://www.google.com/maps" target="_blank"
            class=" flex items-center justify-center md:justify-start space-x-2">
            <img src="../assets/location.svg" alt="Location Icon" class="w-4 h-4">
            <span class="font-semibold hover:underline text-sm">See Us On Google Maps</span>
          </a>
        </div>
        <!-- Phone -->
        <div>
          <p class="font-semibold mb-2">PHONE</p>
          <a href="tel:+639458510079" class="flex items-center justify-center md:justify-start space-x-2">
            <img src="../assets/phone-call.png" alt="Contact Number" class="w-4 h-4">
            <span class="font-semibold text-sm">+63 945 851 0079</span>
          </a>
        </div>
        <!-- Social Media -->
        <div>
          <p class="font-semibold mb-2">FOLLOW US</p>
          <div class="flex justify-center md:justify-start space-x-4 text-xl">
            <a href="https://web.facebook.com/profile.php?id=100086503127265" class="hover:text-white">
              <img src="../assets/facebook.svg" alt="Facebook" class="w-5 h-5">
            </a>
            <a href="https://www.instagram.com/casitadegrands?igsh=Nmc4eHd4bzdyNWNt" class="hover:text-white">
              <img src="../assets/insta.svg" alt="Instagram" class="w-5 h-5">
            </a>
            <a href="https://www.tiktok.com/@casitadegrands?_t=ZS-8towhdTSauO&_r=1" class="hover:text-white">
              <img src="../assets/tiktok.svg" alt="TikTok" class="w-5 h-5">
            </a>
          </div>
        </div>
      </div>

      <!-- Copyright -->
      <div class="text-center text-gray-500 mt-15">
        &copy; Copyright 2024 Casita De Grands - All Rights Reserved
      </div>
    </footer>

  </div>

</body>

</html>


<script>
// Function to show loading state
function showLoading() {
  Swal.fire({
    title: 'Please wait...',
    allowOutsideClick: false,
    showConfirmButton: false,
    willOpen: () => {
      Swal.showLoading();
    }
  });
}

// Modal Toggle
function toggleModal() {
  document.getElementById('loginModal').classList.toggle('hidden');
}

function toggleSignupModal() {
  document.getElementById('signupModal').classList.toggle('hidden');
  document.getElementById('loginModal').classList.add('hidden');
}

function toggleForgotpass() {
  document.getElementById('forgotPasswordModal').classList.toggle('hidden');
}

function switchToLogin() {
  toggleSignupModal();
  toggleModal();
}

// Show Password Toggle
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

// Add form submission handlers
document.addEventListener('DOMContentLoaded', function() {
  // Login form submission
  const loginForm = document.querySelector('form[action="index.php"]');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      if (this.querySelector('[name="login"]')) {
        showLoading();
      }
    });
  }

  // Signup form submission
  const signupForm = document.querySelector('form[action="index.php"]');
  if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
      if (this.querySelector('[name="signup"]')) {
        showLoading();
      }
    });
  }
});
</script>
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

  <?php 
  // Include the navbar
  include('components/navbar.php'); 
  ?>

  <!-- Add margin-top to account for fixed navbar -->
  <div class="mt-20">

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
            <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 24 24">
              <path d="M12 13.5l-11-7V6l11 7 11-7v.5l-11 7zm0 1.5l-11-7V18h22V8l-11 7z"></path>
            </svg>
            <span class="font-semibold text-sm">casitadegrands@gmail.com</span>
          </a>
        </div>
        <!-- Location -->
        <div>
          <p class="font-semibold mb-2">LOCATION</p>
          <a href="https://www.google.com/maps" target="_blank"
            class="flex items-center justify-center md:justify-start space-x-2">
            <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 24 24">
              <path
                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z">
              </path>
            </svg>
            <span class="font-semibold hover:underline text-sm">See Us On Google Maps</span>
          </a>
        </div>
        <!-- Phone -->
        <div>
          <p class="font-semibold mb-2">PHONE</p>
          <a href="tel:+639458510079" class="flex items-center justify-center md:justify-start space-x-2">
            <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 24 24">
              <path
                d="M6.6 10.2c1.2 2.6 3.2 4.7 5.8 5.8l1.9-1.9c.3-.3.7-.4 1.1-.3.9.3 1.8.4 2.7.4.6 0 1 .4 1 1v3c0 .6-.4 1-1 1C9.8 19.2 4.8 14.2 4.8 8c0-.6.4-1 1-1h3c.6 0 1 .4 1 1 .1.9.2 1.8.4 2.7.1.4 0 .8-.3 1.1l-1.9 1.9z">
              </path>
            </svg>
            <span class="font-semibold text-sm">+63 945 851 0079</span>
          </a>
        </div>
        <!-- Social Media -->
        <div>
          <p class="font-semibold mb-2">FOLLOW US</p>
          <div class="flex justify-center md:justify-start space-x-4 text-xl">
            <a href="https://web.facebook.com/profile.php?id=100086503127265" class="hover:text-white">
              <svg class="w-5 h-5 fill-current text-gray-300" viewBox="0 0 24 24">
                <path
                  d="M22 12a10 10 0 1 0-11.6 9.9v-7h-2v-3h2V9.6c0-2 1.2-3.2 3-3.2.9 0 1.8.2 1.8.2v2h-1c-1 0-1.3.6-1.3 1.2V12h2.6l-.4 3h-2.2v7A10 10 0 0 0 22 12z">
                </path>
              </svg>
            </a>
            <a href="https://www.instagram.com/casitadegrands?igsh=Nmc4eHd4bzdyNWNt" class="hover:text-white">
              <svg class="w-5 h-5 fill-current text-gray-300" viewBox="0 0 24 24">
                <path
                  d="M7.5 2C4.4 2 2 4.4 2 7.5v9C2 19.6 4.4 22 7.5 22h9c3.1 0 5.5-2.4 5.5-5.5v-9C22 4.4 19.6 2 16.5 2h-9zm9 2c2 0 3.5 1.5 3.5 3.5v9c0 2-1.5 3.5-3.5 3.5h-9c-2 0-3.5-1.5-3.5-3.5v-9C4 5.5 5.5 4 7.5 4h9zM12 6.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11zm0 2a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7zm5-1.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2z">
                </path>
              </svg>
            </a>
            <a href="https://www.tiktok.com/@casitadegrands?_t=ZS-8towhdTSauO&_r=1" class="hover:text-white">
              <svg class="w-5 h-5 fill-current text-gray-300" viewBox="0 0 24 24">
                <path
                  d="M12 2c1.9 0 3.6.7 5 2 1.4 1.4 2 3.1 2 5h-3c0-.9-.2-1.8-.6-2.6-.4-.8-1-1.5-1.8-2-.8-.5-1.7-.8-2.6-.8-2.6 0-4.7 2.1-4.7 4.7S9.4 14 12 14c1.9 0 3.6-1 4.6-2.5h3.1c-1.2 3.1-4.1 5.5-7.6 5.5-4.4 0-8-3.6-8-8S7.6 2 12 2z">
                </path>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>


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
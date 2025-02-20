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
    
    // Query to check user credentials with proper joins
    $sql = "SELECT 
        u.id,
        u.email,
        u.password,
        u.user_type,
        CASE 
            WHEN u.user_type = 'customer' THEN c.full_name
            WHEN u.user_type = 'staff' THEN s.staff_name
            WHEN u.user_type = 'admin' THEN u.email
        END as display_name,
        CASE 
            WHEN u.user_type = 'customer' THEN c.contact_number
            WHEN u.user_type = 'staff' THEN s.contact_number
            ELSE NULL
        END as contact_number
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
            
            // For admin with default password
            if ($user['user_type'] === 'admin' && $password === 'admin123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['full_name'] = $user['display_name'];
                header('Location: admin/dashboard.php');
                exit();
            }
            // For regular users (staff and customers)
            else if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['full_name'] = $user['display_name'];
                
                if ($user['contact_number']) {
                    $_SESSION['contact_number'] = $user['contact_number'];
                }
                
                // Redirect based on user type
                switch($user['user_type']) {
                    case 'staff':
                        header('Location: staff/staff_dashboard.php');
                        break;
                    case 'customer':
                        header('Location: customer/customer_dashboard.php');
                        break;
                    default:
                        header('Location: index.php');
                }
                exit();
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
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header('Location: index.php');
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // First insert into users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        
        $user_id = $conn->insert_id;

        // Then insert into customers table
        $sql = "INSERT INTO customers (user_id, full_name, contact_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $full_name, $contact_number);
        $stmt->execute();

        $conn->commit();

        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Casita De Grands</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

  <!-- Google Fonts -->
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
  <!-- FontAwesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
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

  <!-- Include Navigation -->
  <?php include 'components/navbar.php'; ?>

  <!-- Add margin-top to account for fixed navbar -->
  <div class="pt-16">
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
    <section class="text-center py-16 px-6">
      <h2 class="text-4xl font-['Ephesis'] mb-8">Discover Your Perfect Stay</h2>
      <div class="max-w-6xl mx-auto relative">
        <!-- Carousel Container -->
        <div class="overflow-hidden relative h-[650px] rounded-lg">
          <div id="carousel" class="flex transition-transform duration-500 ease-in-out h-full">
            <div class="flex-shrink-0 w-full">
              <img src="assets/picture/picture1.jpg" alt="Room 1" class="w-full h-full object-cover">
            </div>
            <div class="flex-shrink-0 w-full">
              <img src="assets/picture/picture2.png" alt="Room 2" class="w-full h-full object-cover">
            </div>
            <div class="flex-shrink-0 w-full">
              <img src="assets/picture/picture3.png" alt="Room 3" class="w-full h-full object-cover">
            </div>
            <div class="flex-shrink-0 w-full">
              <img src="assets/picture/picture4.png" alt="Room 4" class="w-full h-full object-cover">
            </div>
            <div class="flex-shrink-0 w-full">
              <img src="assets/picture/picture5.png" alt="Room 5" class="w-full h-full object-cover">
            </div>

          </div>

        </div>

        <!-- Navigation Buttons -->
        <button onclick="prevSlide()"
          class="absolute top-1/2 left-4 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <button onclick="nextSlide()"
          class="absolute top-1/2 right-4 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <!-- Dots Navigation -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
          <button onclick="goToSlide(0)"
            class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
          <button onclick="goToSlide(1)"
            class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
          <button onclick="goToSlide(2)"
            class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
          <button onclick="goToSlide(3)"
            class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
          <button onclick="goToSlide(4)"
            class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
        </div>

      </div>
  </div>
  </section>

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

      <?php
            // Fetch approved feedbacks with customer details
            $sql = "SELECT f.*, c.full_name 
                    FROM feedbacks f 
                    JOIN customers c ON f.customer_id = c.id 
                    WHERE f.status = 'approved' 
                    ORDER BY f.created_at DESC";
            $result = $conn->query($sql);
            $total_feedbacks = $result ? $result->num_rows : 0;
            ?>

      <!-- Feedback Carousel -->
      <div class="relative overflow-hidden">
        <div id="feedbackCarousel" class="relative w-full">
          <div class="flex transition-transform duration-500 ease-in-out">
            <?php if ($result && $result->num_rows > 0): 
                            $counter = 0;
                            while ($feedback = $result->fetch_assoc()): 
                                if ($counter % 3 == 0): ?>
            <div class="w-full flex-none grid md:grid-cols-3 gap-8 px-4">
              <?php endif; ?>

              <div class="bg-gray-50 p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="flex justify-center mb-4">
                  <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                  <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  <?php endfor; ?>
                </div>

                <p class="text-gray-600 italic mb-6">"<?php echo htmlspecialchars($feedback['message']); ?>"</p>

                <div class="mt-4">
                  <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($feedback['full_name']); ?></p>
                  <p class="text-sm text-gray-500"><?php echo date('F d, Y', strtotime($feedback['created_at'])); ?>
                  </p>
                </div>
              </div>

              <?php 
                                $counter++;
                                if ($counter % 3 == 0 || $counter == $result->num_rows): ?>
            </div>
            <?php endif;
                            endwhile; ?>
            <?php else: ?>
            <div class="col-span-3 text-center text-gray-500">
              <p class="text-lg">No feedback available yet.</p>
              <?php if (!isset($_SESSION['user_id'])): ?>
              <p class="mt-2">
                <a href="#" onclick="toggleModal()" class="text-emerald-600 hover:text-emerald-700">
                  Login to share your experience
                </a>
              </p>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Carousel Controls -->
        <?php if ($total_feedbacks > 3): ?>
        <button
          class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-r-lg hover:bg-opacity-75"
          onclick="prevFeedbackSlide()">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-l-lg hover:bg-opacity-75"
          onclick="nextFeedbackSlide()">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <!-- Carousel Indicators -->
        <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-2 pb-4">
          <?php for ($i = 0; $i < ceil($total_feedbacks / 3); $i++): ?>
          <button onclick="goToFeedbackSlide(<?php echo $i; ?>)"
            class="w-2 h-2 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors duration-200">
          </button>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Feedback Button -->
      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'customer'): ?>
      <div class="mt-12">
        <button onclick="openFeedbackModal()"
          class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
          <span class="mr-2">✨</span>
          Share Your Experience
        </button>
      </div>
      <?php elseif (!isset($_SESSION['user_id'])): ?>
      <div class="mt-12">
        <button onclick="toggleModal()"
          class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-300">
          Login to Share Feedback
        </button>
      </div>
      <?php endif; ?>
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
        <p class="font-semibold mb-4 flex items-center justify-center md:justify-start">
          <i class="fas fa-envelope text-emerald-500 mr-3"></i>
          EMAIL
        </p>
        <a href="mailto:casitadegrands@gmail.com"
          class="hover:text-emerald-500 transition-colors flex items-center justify-center md:justify-start">
          <i class="far fa-envelope text-sm mr-3"></i>
          casitadegrands@gmail.com
        </a>
      </div>

      <!-- Location -->
      <div>
        <p class="font-semibold mb-4 flex items-center justify-center md:justify-start">
          <i class="fas fa-map-marker-alt text-emerald-500 mr-3"></i>
          LOCATION
        </p>
        <a href="https://maps.google.com" target="_blank"
          class="hover:text-emerald-500 transition-colors flex items-center justify-center md:justify-start">
          <i class="fas fa-map-pin text-sm mr-3"></i>
          Purok 7, Muladbucad Grande<br>
          Guinobatan, Albay
        </a>
      </div>

      <!-- Phone -->
      <div>
        <p class="font-semibold mb-4 flex items-center justify-center md:justify-start">
          <i class="fas fa-phone-alt text-emerald-500 mr-3"></i>
          PHONE
        </p>
        <a href="tel:+639458510079"
          class="hover:text-emerald-500 transition-colors flex items-center justify-center md:justify-start">
          <i class="fas fa-phone text-sm mr-3"></i>
          +63 945 851 0079
        </a>
      </div>

      <!-- Social Media -->
      <div>
        <p class="font-semibold mb-4 flex items-center justify-center md:justify-start">
          <i class="fas fa-share-alt text-emerald-500 mr-3"></i>
          FOLLOW US
        </p>
        <div class="flex justify-center md:justify-start space-x-4">
          <a href="https://web.facebook.com/profile.php?id=100086503127265"
            class="hover:text-emerald-500 transition-colors text-2xl">
            <i class="fab fa-facebook"></i>
          </a>
          <a href="https://www.instagram.com/casitadegrands?igsh=Nmc4eHd4bzdyNWNt"
            class="hover:text-emerald-500 transition-colors text-2xl">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="https://www.tiktok.com/@casitadegrands?_t=ZS-8towhdTSauO&_r=1"
            class="hover:text-emerald-500 transition-colors text-2xl">
            <i class="fab fa-tiktok"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Copyright -->
    <div class="text-center mt-8 text-gray-500">
      <p class="flex items-center justify-center">
        <i class="far fa-copyright mr-2"></i>
        2024 Casita De Grands - All Rights Reserved
      </p>
    </div>
  </footer>


  </div>

  <!-- Existing scripts -->
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

  let currentFeedbackSlide = 0;
  const feedbackCarousel = document.getElementById('feedbackCarousel');
  const totalFeedbackSlides = <?php echo ceil($total_feedbacks / 3); ?>;

  function updateFeedbackCarousel() {
    const translateX = currentFeedbackSlide * -100;
    feedbackCarousel.querySelector('.flex').style.transform = `translateX(${translateX}%)`;

    // Update dots
    document.querySelectorAll('.bottom-0 button').forEach((dot, index) => {
      dot.classList.toggle('bg-gray-400', index === currentFeedbackSlide);
      dot.classList.toggle('bg-gray-300', index !== currentFeedbackSlide);
    });
  }

  function nextFeedbackSlide() {
    currentFeedbackSlide = (currentFeedbackSlide + 1) % totalFeedbackSlides;
    updateFeedbackCarousel();
  }

  function prevFeedbackSlide() {
    currentFeedbackSlide = (currentFeedbackSlide - 1 + totalFeedbackSlides) % totalFeedbackSlides;
    updateFeedbackCarousel();
  }

  function goToFeedbackSlide(index) {
    currentFeedbackSlide = index;
    updateFeedbackCarousel();
  }

  // Auto-advance slides every 5 seconds
  let feedbackInterval = setInterval(nextFeedbackSlide, 5000);

  // Pause auto-advance on hover
  feedbackCarousel.addEventListener('mouseenter', () => {
    clearInterval(feedbackInterval);
  });

  // Resume auto-advance when mouse leaves
  feedbackCarousel.addEventListener('mouseleave', () => {
    feedbackInterval = setInterval(nextFeedbackSlide, 5000);
  });

  // Initial update
  updateFeedbackCarousel();
  </script>
  <script>
  let currentIndex = 0;
  const carousel = document.getElementById('carousel');
  const totalSlides = document.querySelectorAll('#carousel > div').length;
  const dots = document.querySelectorAll('.absolute.bottom-4 button');

  function updateCarousel() {
    carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
    // Update dots
    dots.forEach((dot, index) => {
      dot.style.opacity = index === currentIndex ? '1' : '0.5';
    });
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateCarousel();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    updateCarousel();
  }

  function goToSlide(index) {
    currentIndex = index;
    updateCarousel();
  }

  // Auto-advance slides every 5 seconds
  let slideInterval = setInterval(nextSlide, 5000);

  // Pause auto-advance on hover
  carousel.parentElement.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
  });

  // Resume auto-advance when mouse leaves
  carousel.parentElement.addEventListener('mouseleave', () => {
    slideInterval = setInterval(nextSlide, 5000);
  });

  // Initial update
  updateCarousel();
  </script>
</body>

</html>
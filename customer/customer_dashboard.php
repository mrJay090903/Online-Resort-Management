<?php
// Start session at the very beginning of the file, before any output
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
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
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                switch($_SESSION['user_type']) {
                    case 'customer':
                        header('Location: customer/customer_dashboard.php');
                        exit();
                    case 'staff':
                        header('Location: staff/staff_dashboard.php');
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
  <title>Customer Dashboard - Casita de Grands</title>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
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
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/winter-story" rel="stylesheet">
  <style>
  .font-winter-story {
    font-family: 'Winter Story', sans-serif;
  }
  </style>
</head>

<body class="font-sans bg-gray-100" x-data>

  <?php include('components/nav.php'); ?>

  <!-- Hero Section -->
  <section class="relative w-full h-[570px] overflow-hidden">
    <video class="absolute inset-0 w-full h-full object-cover brightness-85" autoplay loop muted playsinline>
      <source src="../videos/bg-vid.mp4" type="video/mp4">
      Your browser does not support the video tag.
    </video>

    <div class="absolute inset-0 flex flex-col justify-center items-center text-white text-center px-4">
      <p class="font-['Raleway'] text-sm uppercase tracking-widest">Welcome To</p>
      <h1 class="text-6xl font-bold font-['playfair']">CASITA DE GRANDS</h1>
      <p class="mt-2 text-lg">Escape to Tranquility, Your Hidden Paradise Awaits</p>
      <a href="new_booking.php"
        class="w-40 mt-6 px-4 py-2 border-2 border-white text-white bg-transparent bg-opacity-40 backdrop-blur-md hover:bg-white hover:text-black transition-all duration-300">
        Book Now
      </a>
    </div>
  </section>

  <!-- Description Section -->
  <section class="text-center py-16 px-6">
    <h2 class="text-4xl font-['Second_Quotes']">Your Escape to Serenity</h2>
    <p class="mt-10 text-gray-600 max-w-3xl mx-auto font-['Ubuntu_Sans']">
      Looking for a relaxing escape? Casita De Grands, hidden away in the lush greenery of Muladbucad Grande,
      Guinobatan, Albay, is the perfect place to unwind. Just minutes from Guinobatan Centro, our resort offers
      a peaceful retreat surrounded by nature.
    </p>
    <p class="mt-4 text-gray-600 max-w-3xl mx-auto">
      Take a dip in our beautiful infinity pool and immerse yourself in the calming view of lush nature all around.
    </p>
  </section>

  <!-- Carousel Section -->
  <section class="text-center py-16 px-6">
    <h2 class="text-4xl font-['Ephesis'] mb-8">Discover Your Perfect Stay</h2>
    <div class="max-w-6xl mx-auto relative">
      <!-- Carousel Container -->
      <div class="overflow-hidden relative h-[400px] rounded-lg">
        <div id="carousel" class="flex transition-transform duration-500 ease-in-out h-full">
          <div class="flex-shrink-0 w-full">
            <img src="../uploads/rooms/1739542980.png" alt="Room 1" class="w-full h-full object-cover">
          </div>
          <div class="flex-shrink-0 w-full">
            <img src="../assets/rooms/room2.jpg" alt="Room 2" class="w-full h-full object-cover">
          </div>
          <div class="flex-shrink-0 w-full">
            <img src="../assets/rooms/room3.jpg" alt="Room 3" class="w-full h-full object-cover">
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
        </div>
      </div>
    </div>
  </section>

  <!-- Feature Section -->
  <section class="bg-gray-100 py-16">
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-4xl font-bold text-gray-800 mb-6">Our Features</h2>
      <p class="text-gray-600 mb-12 text-lg">Discover the luxurious amenities and breathtaking experiences.</p>

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
  <section class="bg-white py-16">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-['Second_Quotes'] text-gray-800 mb-2">Customer's Feedback</h2>
        <p class="text-gray-500 uppercase text-xs tracking-[0.5em] mb-12 font-['Raleway']">Your Opinion Matters</p>

        <?php
        // Check for pending feedback
        $pending_sql = "SELECT f.*, c.full_name 
                       FROM feedbacks f 
                       JOIN customers c ON f.customer_id = c.id 
                       WHERE f.customer_id = (SELECT id FROM customers WHERE user_id = ?) 
                       AND f.status = 'pending'";
        $stmt = $conn->prepare($pending_sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $pending_result = $stmt->get_result();

        if ($pending_result->num_rows > 0) {
            echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                    <p class="text-yellow-800">
                        <span class="font-semibold">Thank you for your feedback!</span> 
                        Your feedback is currently pending approval from our administrators.
                    </p>
                  </div>';
        }

        // Fetch all approved feedbacks
        $sql = "SELECT f.*, c.full_name 
                FROM feedbacks f 
                JOIN customers c ON f.customer_id = c.id 
                WHERE f.status = 'approved' 
                ORDER BY f.created_at DESC";
        $result = $conn->query($sql);
        $feedbacks = $result->fetch_all(MYSQLI_ASSOC);
        $total_feedbacks = count($feedbacks);
        ?>

        <?php if ($total_feedbacks > 0): ?>
            <div class="relative overflow-hidden">
                <!-- Carousel Container -->
                <div id="feedbackCarousel" class="relative w-full">
                    <div class="flex transition-transform duration-500 ease-in-out">
                        <?php
                        // Split feedbacks into groups of 3
                        $feedback_groups = array_chunk($feedbacks, 3);
                        foreach ($feedback_groups as $group):
                        ?>
                            <div class="w-full flex-none grid md:grid-cols-3 gap-8">
                                <?php foreach ($group as $feedback): ?>
                                    <div class="bg-gray-100 p-8 rounded-lg shadow-md">
                                        <!-- Rating Stars -->
                                        <div class="flex justify-center mb-6">
                                            <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                                                <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>

                                        <!-- Feedback Message -->
                                        <p class="italic text-gray-600 mb-6">
                                            "<?php echo htmlspecialchars($feedback['message']); ?>"
                                        </p>

                                        <!-- Customer Name -->
                                        <h3 class="font-semibold text-gray-800 mb-2">
                                            <?php echo htmlspecialchars($feedback['full_name']); ?>
                                        </h3>

                                        <!-- Feedback Date -->
                                        <p class="text-sm text-gray-500">
                                            <?php echo date('F d, Y', strtotime($feedback['created_at'])); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($total_feedbacks > 3): ?>
                    <!-- Navigation Arrows -->
                    <button onclick="prevFeedbackSlide()" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-all -ml-4">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button onclick="nextFeedbackSlide()" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-all -mr-4">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Dots Navigation -->
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 flex space-x-2 mb-4">
                        <?php for ($i = 0; $i < ceil($total_feedbacks / 3); $i++): ?>
                            <button onclick="goToFeedbackSlide(<?php echo $i; ?>)" 
                                    class="w-2 h-2 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors duration-200">
                            </button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Fallback content if no feedbacks -->
            <div class="text-center text-gray-500">
                <p>No approved customer feedbacks yet. Share your experience and be the first!</p>
            </div>
        <?php endif; ?>

        <!-- Share Feedback Button -->
        <div class="mt-12">
            <button onclick="showFeedbackModal()" 
                    class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                <span class="mr-2">✨</span>
                Share Your Experience
            </button>
        </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-800 text-gray-300 py-12">
    <div class="container mx-auto px-6">
      <div class="grid md:grid-cols-4 gap-8 text-center md:text-left">
        <div>
          <h3 class="font-semibold mb-4">Contact Us</h3>
          <p>Phone: +63 945 851 0079</p>
          <p>Email: casitadegrands@gmail.com</p>
        </div>
        <div>
          <h3 class="font-semibold mb-4">Location</h3>
          <p>Purok 7, Muladbucad Grande</p>
          <p>Guinobatan, Albay</p>
        </div>
        <div>
          <h3 class="font-semibold mb-4">Quick Links</h3>
          <ul>
            <li><a href="about-us.php" class="hover:text-white">About Us</a></li>
            <li><a href="my_bookings.php" class="hover:text-white">My Bookings</a></li>
            <li><a href="settings.php" class="hover:text-white">Settings</a></li>
          </ul>
        </div>
        <div>
          <h3 class="font-semibold mb-4">Follow Us</h3>
          <div class="flex justify-center md:justify-start space-x-4">
            <a href="https://facebook.com" class="hover:text-white"><i class="fab fa-facebook"></i></a>
            <a href="https://instagram.com" class="hover:text-white"><i class="fab fa-instagram"></i></a>
            <a href="https://twitter.com" class="hover:text-white"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
      </div>
      <div class="text-center mt-8">
        <p>&copy; 2024 Casita De Grands. All rights reserved.</p>
      </div>
    </div>
  </footer>

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

  <script>
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

  <!-- Add this modal form before the closing body tag -->
  <div id="feedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-300">
          <div class="p-6">
              <h3 class="text-2xl font-semibold text-gray-900 mb-4">Share Your Feedback</h3>
              <form id="feedbackForm" method="POST" action="../handlers/feedback_handler.php" class="space-y-4">
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                      <div class="flex space-x-4">
                          <?php for($i = 1; $i <= 5; $i++): ?>
                          <label class="cursor-pointer">
                              <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" required>
                              <svg class="w-8 h-8 star-rating fill-current peer-checked:text-yellow-400 hover:text-yellow-400 text-gray-300 transition-colors duration-200"
                                  viewBox="0 0 24 24">
                                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                              </svg>
                          </label>
                          <?php endfor; ?>
                      </div>
                  </div>

                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                      <textarea name="message" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                          placeholder="Tell us about your experience..."></textarea>
                  </div>

                  <div class="flex justify-end space-x-3 mt-6">
                      <button type="button" onclick="closeFeedbackModal()"
                          class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                          Cancel
                      </button>
                      <button type="submit"
                          class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors duration-200">
                          Submit Feedback
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- Add this JavaScript for the feedback modal -->
  <script>
  function showFeedbackModal() {
      document.getElementById('feedbackModal').classList.remove('hidden');
  }

  function closeFeedbackModal() {
      const modal = document.getElementById('feedbackModal');
      modal.classList.add('hidden');
      document.getElementById('feedbackForm').reset();
      // Reset star appearance
      document.querySelectorAll('.star-rating').forEach(star => {
          star.classList.remove('text-yellow-400');
          star.classList.add('text-gray-300');
      });
  }

  // Star rating functionality
  document.querySelectorAll('.star-rating').forEach((star, index) => {
      const container = star.parentElement;
      
      container.addEventListener('mouseover', () => {
          // Fill in stars up to the current one
          document.querySelectorAll('.star-rating').forEach((s, i) => {
              if (i <= index) {
                  s.classList.remove('text-gray-300');
                  s.classList.add('text-yellow-400');
              }
          });
      });

      container.addEventListener('mouseout', () => {
          // Reset stars to selected state
          const selectedRating = document.querySelector('input[name="rating"]:checked');
          const selectedIndex = selectedRating ? parseInt(selectedRating.value) - 1 : -1;
          
          document.querySelectorAll('.star-rating').forEach((s, i) => {
              if (i <= selectedIndex) {
                  s.classList.remove('text-gray-300');
                  s.classList.add('text-yellow-400');
              } else {
                  s.classList.remove('text-yellow-400');
                  s.classList.add('text-gray-300');
              }
          });
      });

      container.addEventListener('click', () => {
          const input = container.querySelector('input');
          input.checked = true;
      });
  });

  // Close modal when clicking outside
  document.getElementById('feedbackModal').addEventListener('click', function(e) {
      if (e.target === this) {
          closeFeedbackModal();
      }
  });

  // Form validation and submission
  document.getElementById('feedbackForm').addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent default form submission
      const rating = this.querySelector('input[name="rating"]:checked');
      const message = this.querySelector('textarea[name="message"]').value.trim();

      if (!rating) {
          Swal.fire({
              icon: 'error',
              title: 'Please select a rating',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000
          });
          return;
      }

      if (!message) {
          Swal.fire({
              icon: 'error',
              title: 'Please enter your feedback message',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000
          });
          return;
      }

      // Show loading state
      showLoading();

      // Submit form using fetch
      fetch(this.action, {
          method: 'POST',
          body: new FormData(this)
      })
      .then(response => response.json())
      .then(data => {
          Swal.fire({
              icon: data.success ? 'success' : 'error',
              title: data.message,
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000
          }).then(() => {
              closeFeedbackModal();
              location.reload(); // Refresh the page after showing the message
          });
      })
      .catch(error => {
          Swal.fire({
              icon: 'error',
              title: 'An error occurred. Please try again.',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000
          });
      });
  });
  </script>

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
  </script>

</body>

</html>